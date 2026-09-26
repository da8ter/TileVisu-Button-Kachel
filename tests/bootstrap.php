<?php

declare(strict_types=1);

// Isolierte SDK-Attrappe; nie mit einer laufenden Symcon-Installation verbinden.
const KR_READY = 10103, IPS_KERNELSTARTED = 10001, VM_UPDATE = 10603, MEDIATYPE_IMAGE = 1,
    KL_ERROR = 10205, KL_WARNING = 10204;

// Signaturen wie IPSModuleStrict: eine abweichende Signatur im Modul scheitert schon beim Laden der Klasse,
// ein falscher Argumenttyp (etwa 1 statt true) unter strict_types beim Aufruf.
class IPSModuleStrict
{
    public array $properties = [], $propertyTypes = [], $attributes = [], $buffers = [], $messages = [],
        $references = [], $updates = [], $logs = [], $hooks = [];
    public int $InstanceID = 12345;
    public function Create(): void {}
    public function ApplyChanges(): void {}
    public function Destroy(): void {}
    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void {}
    public function RequestAction(string $Ident, mixed $Value): void {}
    public function GetVisualizationTile(): string { return ''; }
    protected function ProcessHookData(): void {}
    protected function RegisterPropertyInteger(string $k, int $v): void { $this->registerProperty($k, 'integer', $v); }
    protected function RegisterPropertyFloat(string $k, float $v): void { $this->registerProperty($k, 'float', $v); }
    protected function RegisterPropertyBoolean(string $k, bool $v): void { $this->registerProperty($k, 'boolean', $v); }
    protected function RegisterPropertyString(string $k, string $v): void { $this->registerProperty($k, 'string', $v); }
    protected function ReadPropertyInteger(string $k): int { return $this->readProperty($k, 'integer'); }
    protected function ReadPropertyFloat(string $k): float { return $this->readProperty($k, 'float'); }
    protected function ReadPropertyBoolean(string $k): bool { return $this->readProperty($k, 'boolean'); }
    protected function ReadPropertyString(string $k): string { return $this->readProperty($k, 'string'); }
    protected function RegisterAttributeString(string $k, string $v): void { $this->attributes[$k] = $v; }
    protected function ReadAttributeString(string $k): string { return $this->attributes[$k] ?? throw new RuntimeException('Attribute not registered: ' . $k); }
    protected function WriteAttributeString(string $k, string $v): void {
        if (!array_key_exists($k, $this->attributes)) throw new RuntimeException('Attribute not registered: ' . $k);
        $this->attributes[$k] = $v;
    }
    // Nativer Hook: ohne $hookAvailable gilt er als nicht registriert (etwa vor dem Neuladen des Moduls).
    protected function RegisterHook(string $hook): bool { $this->hooks[] = $hook; return $GLOBALS['hookAvailable']; }
    protected function SetVisualizationType(int $type): void {}
    protected function UpdateVisualizationValue(string $value): void { $this->updates[] = $value; }
    protected function GetReferenceList(): array { return array_keys($this->references); }
    protected function RegisterReference(int $id): void { $this->references[$id] = true; }
    protected function UnregisterReference(int $id): void { unset($this->references[$id]); }
    protected function GetMessageList(): array { return $this->messages; }
    protected function RegisterMessage(int $id, int $message): void {
        if (!in_array($message, $this->messages[$id] ?? [], true)) $this->messages[$id][] = $message;
    }
    protected function UnregisterMessage(int $id, int $message): void {
        if (!isset($this->messages[$id])) return;
        $this->messages[$id] = array_values(array_diff($this->messages[$id], [$message]));
        if ($this->messages[$id] === []) unset($this->messages[$id]);
    }
    protected function GetBuffer(string $k): string { return $this->buffers[$k] ?? ''; }
    protected function SetBuffer(string $k, string $v): void { $this->buffers[$k] = $v; }
    protected function LogMessage(string $message, int $type): void { $this->logs[] = $message; }
    protected function SendDebug(string $name, string $data, int $format): void {}
    protected function Translate(string $text): string { return $text; }

    private function registerProperty(string $k, string $type, mixed $v): void
    {
        $this->properties[$k] = $v;
        $this->propertyTypes[$k] = $type;
    }

    // Unbekannte Eigenschaft oder falscher Typ ist ein Fehler, keine stille Umwandlung.
    private function readProperty(string $k, string $type): mixed
    {
        if (!isset($this->propertyTypes[$k])) throw new RuntimeException('Property not registered: ' . $k);
        if ($this->propertyTypes[$k] !== $type) throw new RuntimeException("Property $k is {$this->propertyTypes[$k]}, read as $type");
        return $this->properties[$k];
    }
}

$variables = $profiles = $media = $actions = $options = [];
$runlevel = KR_READY; $hookAvailable = false; $mediaContentCalls = 0;

// Neue, leere Welt je Prüfabschnitt.
function world(): void
{
    $GLOBALS['variables'] = $GLOBALS['profiles'] = $GLOBALS['media'] = $GLOBALS['actions'] = $GLOBALS['options'] = [];
    $GLOBALS['runlevel'] = KR_READY;
    $GLOBALS['hookAvailable'] = false;
    $GLOBALS['mediaContentCalls'] = 0;
}
function variable(int $id, mixed $value, string $formatted, string $name, string $profile = '', string $customProfile = '', string $icon = ''): void
{
    $GLOBALS['variables'][$id] = [
        'VariableType' => match (true) { is_bool($value) => 0, is_int($value) => 1, is_float($value) => 2, default => 3 },
        'VariableProfile' => $profile, 'VariableCustomProfile' => $customProfile,
        'value' => $value, 'formatted' => $formatted, 'name' => $name, 'icon' => $icon,
    ];
}
function profile(string $name, array $associations, string $icon = ''): void
{
    $GLOBALS['profiles'][$name] = ['Associations' => $associations, 'Icon' => $icon];
}
function assoc(float $value, string $name, string $icon, int $color): array
{
    return ['Value' => $value, 'Name' => $name, 'Icon' => $icon, 'Color' => $color];
}
function image(int $id, string $file, string $bytes, int $type = MEDIATYPE_IMAGE): void
{
    $GLOBALS['media'][$id] = ['MediaType' => $type, 'MediaFile' => $file, 'content' => base64_encode($bytes)];
}

function IPS_GetKernelRunlevel(): int { return $GLOBALS['runlevel']; }
function IPS_GetOption(string $option): mixed { return $GLOBALS['options'][$option] ?? 1048576; }
function IPS_VariableExists(int $id): bool { return isset($GLOBALS['variables'][$id]); }
function IPS_MediaExists(int $id): bool { return isset($GLOBALS['media'][$id]); }
function IPS_GetVariable(int $id): array { return $GLOBALS['variables'][$id] ?? throw new RuntimeException('Variable does not exist: ' . $id); }
function IPS_GetObject(int $id): array { $v = IPS_GetVariable($id); return ['ObjectName' => $v['name'], 'ObjectIcon' => $v['icon']]; }
function IPS_GetName(int $id): string { return IPS_GetVariable($id)['name']; }
function IPS_GetMedia(int $id): array { return $GLOBALS['media'][$id] ?? throw new RuntimeException('Media does not exist: ' . $id); }
function IPS_GetMediaContent(int $id): string { $GLOBALS['mediaContentCalls']++; return IPS_GetMedia($id)['content']; }
function IPS_VariableProfileExists(string $name): bool { return isset($GLOBALS['profiles'][$name]); }
function IPS_GetVariableProfile(string $name): array { return $GLOBALS['profiles'][$name] ?? throw new RuntimeException('Profile does not exist: ' . $name); }
function GetValue(int $id): mixed { return IPS_GetVariable($id)['value']; }
function GetValueFormatted(int $id): string { return IPS_GetVariable($id)['formatted']; }
function RequestAction(int $id, mixed $value): bool { $GLOBALS['actions'][] = [$id, $value]; return true; }

function check(bool $condition, string $label): void
{
    if (!$condition) throw new RuntimeException('FAIL: ' . $label);
    echo 'PASS: ' . $label . PHP_EOL;
}

// WIDGETS_MODULE wählt eine andere Fassung der Kachel (Gegenprobe gegen den Stand vor dem Nachrichtenfilter).
require getenv('WIDGETS_MODULE') ?: __DIR__ . '/../Widgets/module.php';

// Wie Symcons HookInstance: macht ProcessHookData aufrufbar (öffentlich, : void). Fängt Kopfzeilen und Status ab,
// damit der Hook ohne Webserver prüfbar ist, und zählt, wie oft der Standard-Hintergrund kodiert wird.
class ProbeTile extends TileVisuWidgetsTile
{
    public array $sent = [];
    public int $status = 200;
    public int $defaultEncodings = 0;
    public function ProcessHookData(): void { parent::ProcessHookData(); }
    protected function SendHeader(string $header): void { $this->sent[] = $header; }
    protected function SendStatus(int $code): void { $this->status = $code; }
    protected function DefaultBackgroundImage(): string { $this->defaultEncodings++; return parent::DefaultBackgroundImage(); }
    public function hook(array $get, array $server = []): string
    {
        $_GET = $get;
        $_SERVER['HTTP_IF_NONE_MATCH'] = $server['HTTP_IF_NONE_MATCH'] ?? '';
        $this->sent = [];
        $this->status = 200;
        ob_start();
        try {
            $this->ProcessHookData();
        } finally {
            $body = (string) ob_get_clean();
        }
        return $body;
    }
}

function tile(int $instanceID = 12345): ProbeTile
{
    $m = new ProbeTile();
    $m->InstanceID = $instanceID;
    $m->Create();
    return $m;
}
function latest(IPSModuleStrict $m): array { return json_decode(end($m->updates), true, 512, JSON_THROW_ON_ERROR); }
// Startzustand aus dem Kacheldokument: handleMessage(<JSON als JS-String>) am Ende.
function snapshot(TileVisuWidgetsTile $m): array
{
    $html = $m->GetVisualizationTile();
    $prefix = '<script>handleMessage(';
    $start = strrpos($html, $prefix);
    if ($start === false || !str_ends_with($html, ')</script>')) throw new RuntimeException('Missing initial state');
    $literal = substr($html, $start + strlen($prefix), -strlen(')</script>'));
    return json_decode(json_decode($literal, true, 512, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
}
function query(string $url): array
{
    parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
    return $q;
}
function sentHeader(ProbeTile $m, string $name): ?string
{
    foreach ($m->sent as $header) {
        if (str_starts_with($header, $name . ': ')) return substr($header, strlen($name) + 2);
    }
    return null;
}
