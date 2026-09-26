<?php

declare(strict_types=1);

// Regressionstests ohne laufendes Symcon: Module Strict, Payload wie bisher, Hintergrund über den eigenen Hook.
require __DIR__ . '/bootstrap.php';
set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$source = file_get_contents(__DIR__ . '/../Widgets/module.php');
$html = file_get_contents(__DIR__ . '/../Widgets/module.html');
$png = file_get_contents(__DIR__ . '/../imgs/kachelhintergrund1.png');
$defaultUri = 'data:image/png;base64,' . base64_encode($png);

// --- Module Strict -------------------------------------------------------------------------------------
check(str_starts_with($source, "<?php\n\ndeclare(strict_types=1);\n"), 'strict_types is the first statement');
check(get_parent_class(TileVisuWidgetsTile::class) === 'IPSModuleStrict', 'Module extends IPSModuleStrict');
$untyped = [];
foreach ((new ReflectionClass(TileVisuWidgetsTile::class))->getMethods() as $method) {
    if ($method->getDeclaringClass()->getName() !== TileVisuWidgetsTile::class) continue;
    $typed = $method->hasReturnType();
    foreach ($method->getParameters() as $parameter) $typed = $typed && $parameter->hasType();
    if (!$typed) $untyped[] = $method->getName();
}
check($untyped === [], 'Every module method has typed parameters and a return type');
check((string) (new ReflectionMethod(TileVisuWidgetsTile::class, 'ProcessHookData'))->getReturnType() === 'void',
    'ProcessHookData returns void, compatible with the HookInstance overlay');
try {
    (new class extends IPSModuleStrict { public function probe(): void { $this->RegisterPropertyBoolean('X', 1); } })->probe();
    throw new LogicException('int accepted as bool');
} catch (TypeError $e) {
    check(true, 'SDK double enforces strict types (RegisterPropertyBoolean with 1 fails)');
}

world();
$m = tile();
$expectedTypes = ['bgImage' => 'integer', 'BG_Off' => 'boolean', 'Schriftgroesse' => 'float', 'Bildtransparenz' => 'float',
    'Kachelhintergrundfarbe' => 'integer', 'InfoMenueSchriftfarbe' => 'integer'];
for ($i = 1; $i <= 10; $i++) {
    $expectedTypes += ["Schalter$i" => 'integer', "Schalter{$i}Schriftgroesse" => 'float', "Schalter{$i}Breite" => 'float', "Schalter{$i}AltName" => 'string'];
}
foreach (['NameSwitch', 'IconSwitch', 'VarIconSwitch', 'AssoSwitch'] as $flag) {
    for ($i = 1; $i <= 10; $i++) $expectedTypes["Schalter$i$flag"] = 'boolean';
}
check($m->propertyTypes === $expectedTypes, 'Create registers the same 86 properties with the same types');
check($m->properties['BG_Off'] === true && $m->properties['Schalter1NameSwitch'] === true && $m->properties['Schalter10IconSwitch'] === true
    && $m->properties['Schalter1VarIconSwitch'] === false && $m->properties['Schalter10AssoSwitch'] === true,
    'Boolean defaults are real booleans with the old values');
check($m->properties['Schriftgroesse'] === 1.0 && $m->properties['Bildtransparenz'] === 0.7 && $m->properties['Schalter5Breite'] === 100.0
    && $m->properties['Kachelhintergrundfarbe'] === 0 && $m->properties['InfoMenueSchriftfarbe'] === 0xFFFFFF && $m->properties['bgImage'] === 0,
    'Numeric defaults unchanged');

$runlevel = 0;
$m->ApplyChanges();
check($m->updates === [] && ($m->messages[0] ?? []) === [IPS_KERNELSTARTED], 'Before KR_READY nothing is sent, only IPS_KERNELSTARTED is subscribed');
$runlevel = KR_READY;
$m->MessageSink(0, 0, IPS_KERNELSTARTED, []);
check(count($m->updates) === 1 && !isset($m->messages[0]), 'IPS_KERNELSTARTED runs ApplyChanges and drops the kernel subscription');
check($m->references === [] && $m->messages === [], 'Nothing selected: no references and no subscriptions on ID 0');

// --- Erstaufbau und Payload wie bisher -----------------------------------------------------------------
world();
profile('Switch.Color', [assoc(0, 'Aus', 'Power', 0xFF0000), assoc(1, 'An', 'Bulb', 0x00FF00)], 'Light');
profile('Switch.NoIcon', [assoc(0, 'Aus', '', -1), assoc(1, 'An', '', -1)]);
for ($i = 1; $i <= 10; $i++) variable(100 + $i, true, 'An', 'Schalter ' . $i, 'Switch.Color');
variable(103, false, 'Aus', 'Ventil', 'Switch.NoIcon');
variable(107, true, 'An', 'Tür', 'Switch.Color', '', 'Door');
$m = tile();
for ($i = 1; $i <= 10; $i++) $m->properties["Schalter$i"] = 100 + $i;
$m->properties['Schalter8'] = 999; // gelöschte Variable
$m->properties['Schalter1Breite'] = 120.5;
$m->properties['Schalter2NameSwitch'] = false;
$m->properties['Schalter4AssoSwitch'] = false;
$m->properties['Schalter5IconSwitch'] = false;
$m->properties['Schalter6VarIconSwitch'] = true; // Variable ohne eigenes Icon
$m->properties['Schalter7VarIconSwitch'] = true;
$m->properties['Schalter9AltName'] = 'Neun';
$m->properties['Kachelhintergrundfarbe'] = -1;
$m->properties['Schriftgroesse'] = 1.5;
$m->ApplyChanges();
$state = latest($m);
$expected = [];
for ($i = 1; $i <= 10; $i++) {
    if ($i === 8) continue;
    array_push($expected, "schalter$i", "schalter{$i}breite", "schalter{$i}color");
    if ($i !== 2) $expected[] = "schalter{$i}name";
    if (!in_array($i, [3, 5, 6], true)) $expected[] = "schalter{$i}icon";
    if ($i !== 4) $expected[] = "schalter{$i}asso";
}
array_push($expected, 'hintergrundfarbe', 'infomenueschriftfarbe', 'schriftgroesse', 'transparenz');
for ($i = 1; $i <= 10; $i++) $expected[] = "schalter{$i}altname";
$expected[] = 'bgimage';
check(array_keys($state) === $expected, 'Full update carries the same keys in the same order as before');
check(array_keys(snapshot($m)) === $expected, 'Initial tile state carries the same keys in the same order');
check($state['schalter1'] === 'An' && $state['schalter1breite'] === 120.5 && $state['schalter1color'] === '00FF00'
    && $state['schalter1name'] === 'Schalter 1' && $state['schalter1icon'] === 'Bulb' && $state['schalter1asso'] === 'An',
    'Switch values as before');
check($state['schalter3color'] === '' && $state['schalter7icon'] === 'Door' && $state['schalter9altname'] === 'Neun' && $state['schalter1altname'] === '',
    'Colors, variable icons and alternative names as before');
check($state['hintergrundfarbe'] === '#FFFFFFFFFFFFFFFF' && $state['infomenueschriftfarbe'] === '#FFFFFF'
    && $state['schriftgroesse'] === 1.5 && $state['transparenz'] === 0.7, 'Tile settings as before, transparent marker kept');
check($state['bgimage'] === $defaultUri, 'Default background as before (hook not registered)');
$watched = [101, 102, 103, 104, 105, 106, 107, 999, 109, 110];
check(array_keys($m->references) === $watched && array_keys($m->messages) === $watched
    && array_unique(array_map('json_encode', $m->messages)) === [101 => json_encode([VM_UPDATE])],
    'References and VM_UPDATE only for selected objects');

$m->updates = [];
$m->MessageSink(0, 101, VM_UPDATE, []);
check(count($m->updates) === 2 && json_decode($m->updates[0], true) === ['Schalter1' => 'An'], 'Variable update sends the formatted value first');
check(array_keys(json_decode($m->updates[1], true)) === ['Schalter1Color', 'Schalter1name', 'Schalter1icon', 'Schalter1asso', 'Schalter1AltName'],
    'Then color, name, icon, value and alternative name as before');
$m->updates = [];
$m->MessageSink(0, 102, VM_UPDATE, []);
check(array_keys(json_decode($m->updates[1], true)) === ['Schalter2Color', 'Schalter2icon', 'Schalter2asso', 'Schalter2AltName'],
    'Switch settings apply to updates as before');
$m->updates = [];
$m->MessageSink(0, 555, VM_UPDATE, []);
$m->MessageSink(0, 101, 10505, []);
check($m->updates === [], 'Unrelated senders and messages are ignored');
$m->properties['Schalter4'] = 101;
$m->ApplyChanges();
$m->updates = [];
$m->MessageSink(0, 101, VM_UPDATE, []);
check(count($m->updates) === 4 && json_decode($m->updates[2], true) === ['Schalter4' => 'An'], 'A variable used by two switches updates both');
check(array_keys(json_decode($m->updates[3], true)) === ['Schalter1Color', 'Schalter1name', 'Schalter1icon', 'Schalter1asso', 'Schalter1AltName',
    'Schalter4Color', 'Schalter4name', 'Schalter4icon', 'Schalter4AltName'], 'The second message repeats the first as before');

$m->properties['Schalter1AltName'] = '</script><script>alert(1)</script>';
$m->ApplyChanges();
$document = $m->GetVisualizationTile();
check(str_starts_with($document, $html), 'Tile document is module.html unchanged plus the initial script');
check(substr_count($document, '</script>') === substr_count($html, '</script>') + 1, 'An alternative name cannot close the initial script');
check(snapshot($m)['schalter1altname'] === '</script><script>alert(1)</script>', 'The alternative name arrives unchanged');
variable(110, true, "Ung\xFCltig", "Ung\xFCltig", 'Switch.Color');
$m->ApplyChanges();
check(latest($m)['schalter10name'] === "Ung\u{FFFD}ltig", 'Invalid UTF-8 is replaced instead of losing the whole update');
$m->properties['Schriftgroesse'] = INF;
$m->ApplyChanges();
check(end($m->updates) === '{}' && $m->logs !== [], 'An unencodable update becomes {} with a log entry instead of a TypeError');
$m->properties['Schriftgroesse'] = 1.0;

$m->RequestAction('Schalter1', 1);
check($actions === [[101, false]], 'RequestAction toggles the switch variable');
$actions = [];
$m->RequestAction('Schalter8', 6);
check($actions === [], 'A deleted switch variable is not touched');
foreach (['bgImage', 'Schalter11', 'Schriftgroesse', ''] as $ident) {
    try {
        $m->RequestAction($ident, 1);
        throw new LogicException('Ident accepted');
    } catch (Exception $e) {
        check(str_starts_with($e->getMessage(), 'Invalid ident:') && $actions === [], "Ident \"$ident\" is rejected");
    }
}

// --- Hintergrund über den eigenen Hook -----------------------------------------------------------------
world();
$hookAvailable = true;
$m = tile();
check($m->hooks === ['widgetsimages/12345'] && $m->buffers['ImageHook'] === '1', 'Hook is registered natively in Create');
check($m->attributes['ImageHookToken'] === '', 'No token before ApplyChanges');
$m->ApplyChanges();
$token = $m->attributes['ImageHookToken'];
check(strlen($token) === 32 && ctype_xdigit($token), 'ApplyChanges creates a 128-bit hook token');
$state = snapshot($m);
$version = substr(hash('sha256', $defaultUri), 0, 16);
check($state['bgimage'] === '/hook/widgetsimages/12345?k=bgimage&v=' . $version . '&t=' . $token, 'Default background is sent as versioned hook address');
check(latest($m)['bgimage'] === $state['bgimage'], 'The update message uses the same address');
$document = $m->GetVisualizationTile();
check(!str_contains($document, 'base64'), 'Tile document carries no Base64 image');
check(strlen($document) < strlen($html) + 1000, 'Tile document is module.html plus a small initial script');
$m->ApplyChanges();
check($m->attributes['ImageHookToken'] === $token, 'Token stays stable across ApplyChanges');

$q = query($state['bgimage']);
$body = $m->hook($q);
check($m->status === 200 && $body === $png, 'Hook delivers the background bytes');
check(sentHeader($m, 'Content-Type') === 'image/png' && sentHeader($m, 'Content-Length') === (string) strlen($png), 'Hook sends image type and length');
check(sentHeader($m, 'Cache-Control') === 'private, max-age=31536000, immutable', 'Current version may be cached long, privately');
check(sentHeader($m, 'ETag') === '"' . $version . '"' && sentHeader($m, 'X-Content-Type-Options') === 'nosniff', 'ETag is the version, nosniff is set');
$body = $m->hook($q, ['HTTP_IF_NONE_MATCH' => '"' . $version . '"']);
check($m->status === 304 && $body === '' && sentHeader($m, 'Content-Type') === null, 'Matching If-None-Match answers 304 without body');
$body = $m->hook(['k' => 'bgimage', 'v' => 'veraltet', 't' => $token]);
check($m->status === 200 && $body === $png && sentHeader($m, 'Cache-Control') === 'no-cache', 'Outdated address gets the current content uncached');
$m->hook(['k' => 'bgimage', 'v' => 'veraltet', 't' => $token], ['HTTP_IF_NONE_MATCH' => '"' . $version . '"']);
check($m->status === 200, 'No 304 for an outdated address');
$m->hook(['k' => 'bgimage', 't' => $token]);
check($m->status === 200 && sentHeader($m, 'Cache-Control') === 'no-cache', 'Address without version is not cached');
$rejected = ['wrong token' => ['k' => 'bgimage', 'v' => $version, 't' => str_repeat('0', 32)], 'missing token' => ['k' => 'bgimage', 'v' => $version],
    'empty token' => ['k' => 'bgimage', 't' => ''], 'token as array' => ['k' => 'bgimage', 't' => [$token]]];
foreach ($rejected as $label => $get) {
    $body = $m->hook($get);
    check($m->status === 403 && $body === '' && $m->sent === [], "Hook rejects the request with 403 ($label)");
}
$unknown = ['unknown key' => ['k' => 'schalter1', 't' => $token], 'missing key' => ['t' => $token],
    'key in other case' => ['k' => 'BGIMAGE', 't' => $token], 'key as array' => ['k' => ['bgimage'], 't' => $token]];
foreach ($unknown as $label => $get) {
    $body = $m->hook($get);
    check($m->status === 404 && $body === '' && $m->sent === [], "Hook answers 404 ($label)");
}

image(500, 'media/500.jpg', 'jpeg-bytes-1');
$m->properties['bgImage'] = 500;
$m->ApplyChanges();
$address = latest($m)['bgimage'];
check(str_starts_with($address, '/hook/widgetsimages/12345?k=bgimage&v=') && $address !== $state['bgimage'], 'Own background image gets its own address');
check($m->hook(query($address)) === 'jpeg-bytes-1' && sentHeader($m, 'Content-Type') === 'image/jpeg', 'Hook delivers the own image with its type');
image(500, 'media/500.jpg', 'jpeg-bytes-2');
$m->ApplyChanges();
$changed = latest($m)['bgimage'];
check($changed !== $address && $m->hook(query($changed)) === 'jpeg-bytes-2', 'Changed image content gets a new address');
check($m->hook(query($address)) === 'jpeg-bytes-2' && sentHeader($m, 'Cache-Control') === 'no-cache', 'The old address delivers the new content uncached');
image(500, 'media/500.JPG', 'jpeg-bytes-3');
$m->ApplyChanges();
check(!isset(latest($m)['bgimage']), 'Unsupported file name stays without background as before');
$m->hook(['k' => 'bgimage', 't' => $token]);
check($m->status === 404, 'Hook answers 404 while no background is shown');

// --- Rückfall ohne Hook --------------------------------------------------------------------------------
world();
image(500, 'media/500.jpg', 'jpeg-bytes');
$n = tile(12346);
$n->ApplyChanges();
check($n->hooks === ['widgetsimages/12346'] && ($n->buffers['ImageHook'] ?? null) === '', 'An unregistered hook is remembered as inactive');
check(latest($n)['bgimage'] === $defaultUri && snapshot($n)['bgimage'] === $defaultUri, 'Without hook the default background stays embedded exactly as before');
check($n->attributes['ImageHookToken'] === '', 'Without hook no token is created');
$n->properties['bgImage'] = 500;
$n->ApplyChanges();
check(latest($n)['bgimage'] === 'data:image/jpeg;base64,' . base64_encode('jpeg-bytes'), 'Without hook the own image stays embedded exactly as before');
$body = $n->hook(['k' => 'bgimage', 't' => '']);
check($n->status === 403 && $body === '', 'Without hook the endpoint answers nothing');

// --- Bilder über der Ausgabegrenze bleiben eingebettet -------------------------------------------------
world();
$hookAvailable = true;
$options['ScriptOutputBufferLimit'] = 32768; // kleiner als der Standard-Hintergrund
$m = tile();
$m->ApplyChanges();
check(latest($m)['bgimage'] === $defaultUri && snapshot($m)['bgimage'] === $defaultUri, 'Default background above the hook output limit stays embedded');
$options['ScriptOutputBufferLimit'] = 1024 + 3000; // 3000 Byte Nutzlast
image(500, 'big.png', str_repeat('x', 3000));
$m->properties['bgImage'] = 500;
$m->ApplyChanges();
check(str_starts_with(latest($m)['bgimage'], '/hook/widgetsimages/12345?k=bgimage&v='), 'Image exactly at the limit goes through the hook');
image(500, 'big.png', str_repeat('x', 3001));
$m->ApplyChanges();
check(latest($m)['bgimage'] === 'data:image/png;base64,' . base64_encode(str_repeat('x', 3001)), 'One byte above the limit stays embedded');
unset($options['ScriptOutputBufferLimit']);
$m->ApplyChanges();
check(str_starts_with(latest($m)['bgimage'], '/hook/'), 'With the factory limit (1 MiB) it goes through the hook again');

// --- Kein Hintergrund wird kodiert, wenn keiner angezeigt wird -----------------------------------------
world();
variable(101, true, 'An', 'Licht');
$m = tile();
$m->properties['BG_Off'] = false;
$m->properties['Schalter1'] = 101;
$m->ApplyChanges();
$state = snapshot($m);
check(!isset(latest($m)['bgimage']) && !isset($state['bgimage']), 'Without background the payload carries no bgimage key, as before');
check($m->defaultEncodings === 0 && $mediaContentCalls === 0, 'Without background nothing is read or encoded (update and tile)');
$m->updates = [];
$m->MessageSink(0, 101, VM_UPDATE, []);
check(count($m->updates) === 2 && $m->defaultEncodings === 0 && $mediaContentCalls === 0, 'Variable updates never encode the background');
$m->properties['BG_Off'] = true;
$m->ApplyChanges();
check($m->defaultEncodings === 1 && latest($m)['bgimage'] === $defaultUri, 'A shown default background is encoded once per full update');
$m->MessageSink(0, 101, VM_UPDATE, []);
check($m->defaultEncodings === 1, 'Variable updates do not encode the shown background either');
foreach (['own.png' => [MEDIATYPE_IMAGE, 1], 'own.svg' => [MEDIATYPE_IMAGE, 0], 'not-an-image.png' => [0, 0]] as $file => [$type, $reads]) {
    image(500, $file, 'own', $type);
    $m->properties['bgImage'] = 500;
    $m->defaultEncodings = 0;
    $mediaContentCalls = 0;
    $m->ApplyChanges();
    check($m->defaultEncodings === 0 && $mediaContentCalls === $reads && isset(latest($m)['bgimage']) === ($reads === 1),
        "Selected media $file: default background not encoded, media read only if shown");
}
world();
$hookAvailable = true;
$m = tile();
$m->ApplyChanges();
$m->defaultEncodings = 0;
$m->hook(['k' => 'bgimage', 't' => str_repeat('f', 32)]);
$m->hook(['k' => 'schalter1', 't' => $m->attributes['ImageHookToken']]);
check($m->defaultEncodings === 0, 'Rejected hook requests encode nothing');

echo 'OK' . PHP_EOL;
