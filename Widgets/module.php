<?php

declare(strict_types=1);

class TileVisuWidgetsTile extends IPSModuleStrict
{
    // Idents der Darstellung: jeder Schalter schaltet die Variable seiner gleichnamigen Eigenschaft um
    private const SWITCHES = [
        'Schalter1', 'Schalter2', 'Schalter3', 'Schalter4', 'Schalter5',
        'Schalter6', 'Schalter7', 'Schalter8', 'Schalter9', 'Schalter10'
    ];
    // Beobachtete Objekte (Referenz und VM_UPDATE), in der bisherigen Reihenfolge
    private const WATCHED_PROPERTIES = ['bgImage', ...self::SWITCHES];
    // Unterstützte Hintergrundbilder nach Dateiendung: wie bisher genau diese, klein geschriebenen Endungen
    private const IMAGE_TYPES = [
        'bmp' => 'image/bmp', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif', 'png' => 'image/png', 'ico' => 'image/x-icon'
    ];
    private const DEFAULT_BACKGROUND = __DIR__ . '/../imgs/kachelhintergrund1.png';

    public function Create(): void
    {
        // Nie diese Zeile löschen!
        parent::Create();


        // Drei Eigenschaften für die dargestellten Zähler
        $this->RegisterPropertyInteger("bgImage", 0);
        $this->RegisterPropertyBoolean('BG_Off', true);
        $this->RegisterPropertyFloat('Schriftgroesse', 1);
        $this->RegisterPropertyFloat('Bildtransparenz', 0.7);
        $this->RegisterPropertyInteger('Kachelhintergrundfarbe', 0x000000);
        $this->RegisterPropertyInteger('InfoMenueSchriftfarbe', 0xFFFFFF);
        $this->RegisterPropertyInteger('Schalter1', 0);
        $this->RegisterPropertyFloat('Schalter1Schriftgroesse', 1);
        $this->RegisterPropertyFloat('Schalter1Breite', 100);
        $this->RegisterPropertyString('Schalter1AltName', '');
        $this->RegisterPropertyInteger('Schalter2', 0);
        $this->RegisterPropertyFloat('Schalter2Schriftgroesse', 1);
        $this->RegisterPropertyFloat('Schalter2Breite', 100);
        $this->RegisterPropertyString('Schalter2AltName', '');
        $this->RegisterPropertyInteger('Schalter3', 0);
        $this->RegisterPropertyFloat('Schalter3Schriftgroesse', 1);
        $this->RegisterPropertyFloat('Schalter3Breite', 100);
        $this->RegisterPropertyString('Schalter3AltName', '');
        $this->RegisterPropertyInteger('Schalter4', 0);
        $this->RegisterPropertyFloat('Schalter4Schriftgroesse', 1);
        $this->RegisterPropertyFloat('Schalter4Breite', 100);
        $this->RegisterPropertyString('Schalter4AltName', '');
        $this->RegisterPropertyInteger('Schalter5', 0);
        $this->RegisterPropertyFloat('Schalter5Schriftgroesse', 1);
        $this->RegisterPropertyFloat('Schalter5Breite', 100);
        $this->RegisterPropertyString('Schalter5AltName', '');
        $this->RegisterPropertyInteger('Schalter6', 0);
        $this->RegisterPropertyFloat('Schalter6Schriftgroesse', 1);
        $this->RegisterPropertyFloat('Schalter6Breite', 100);
        $this->RegisterPropertyString('Schalter6AltName', '');
        $this->RegisterPropertyInteger('Schalter7', 0);
        $this->RegisterPropertyFloat('Schalter7Schriftgroesse', 1);
        $this->RegisterPropertyFloat('Schalter7Breite', 100);
        $this->RegisterPropertyString('Schalter7AltName', '');
        $this->RegisterPropertyInteger('Schalter8', 0);
        $this->RegisterPropertyFloat('Schalter8Schriftgroesse', 1);
        $this->RegisterPropertyFloat('Schalter8Breite', 100);
        $this->RegisterPropertyString('Schalter8AltName', '');
        $this->RegisterPropertyInteger('Schalter9', 0);
        $this->RegisterPropertyFloat('Schalter9Schriftgroesse', 1);
        $this->RegisterPropertyFloat('Schalter9Breite', 100);
        $this->RegisterPropertyString('Schalter9AltName', '');
        $this->RegisterPropertyInteger('Schalter10', 0);
        $this->RegisterPropertyFloat('Schalter10Schriftgroesse', 1);
        $this->RegisterPropertyFloat('Schalter10Breite', 100);
        $this->RegisterPropertyString('Schalter10AltName', '');
        $this->RegisterPropertyBoolean('Schalter1NameSwitch', true);
        $this->RegisterPropertyBoolean('Schalter2NameSwitch', true);
        $this->RegisterPropertyBoolean('Schalter3NameSwitch', true);
        $this->RegisterPropertyBoolean('Schalter4NameSwitch', true);
        $this->RegisterPropertyBoolean('Schalter5NameSwitch', true);
        $this->RegisterPropertyBoolean('Schalter6NameSwitch', true);
        $this->RegisterPropertyBoolean('Schalter7NameSwitch', true);
        $this->RegisterPropertyBoolean('Schalter8NameSwitch', true);
        $this->RegisterPropertyBoolean('Schalter9NameSwitch', true);
        $this->RegisterPropertyBoolean('Schalter10NameSwitch', true);
        $this->RegisterPropertyBoolean('Schalter1IconSwitch', true);
        $this->RegisterPropertyBoolean('Schalter2IconSwitch', true);
        $this->RegisterPropertyBoolean('Schalter3IconSwitch', true);
        $this->RegisterPropertyBoolean('Schalter4IconSwitch', true);
        $this->RegisterPropertyBoolean('Schalter5IconSwitch', true);
        $this->RegisterPropertyBoolean('Schalter6IconSwitch', true);
        $this->RegisterPropertyBoolean('Schalter7IconSwitch', true);
        $this->RegisterPropertyBoolean('Schalter8IconSwitch', true);
        $this->RegisterPropertyBoolean('Schalter9IconSwitch', true);
        $this->RegisterPropertyBoolean('Schalter10IconSwitch', true);
        $this->RegisterPropertyBoolean('Schalter1VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Schalter2VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Schalter3VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Schalter4VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Schalter5VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Schalter6VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Schalter7VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Schalter8VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Schalter9VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Schalter10VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Schalter1AssoSwitch', true);
        $this->RegisterPropertyBoolean('Schalter2AssoSwitch', true);
        $this->RegisterPropertyBoolean('Schalter3AssoSwitch', true);
        $this->RegisterPropertyBoolean('Schalter4AssoSwitch', true);
        $this->RegisterPropertyBoolean('Schalter5AssoSwitch', true);
        $this->RegisterPropertyBoolean('Schalter6AssoSwitch', true);
        $this->RegisterPropertyBoolean('Schalter7AssoSwitch', true);
        $this->RegisterPropertyBoolean('Schalter8AssoSwitch', true);
        $this->RegisterPropertyBoolean('Schalter9AssoSwitch', true);
        $this->RegisterPropertyBoolean('Schalter10AssoSwitch', true);
        // Visualisierungstyp auf 1 setzen, da wir HTML anbieten möchten
        $this->SetVisualizationType(1);
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();

        // Kein Heavy Work vor KR_READY: Referenzen, Nachrichten und Variablenzugriffe erst mit bereitem Kernel
        if (IPS_GetKernelRunlevel() !== KR_READY) {
            $this->RegisterMessage(0, IPS_KERNELSTARTED);
            return;
        }

        //Referenzen Registrieren (0 = nichts ausgewählt)
        foreach ($this->GetReferenceList() as $ref) {
            $this->UnregisterReference($ref);
        }
        foreach (self::WATCHED_PROPERTIES as $VariableProperty) {
            $id = $this->ReadPropertyInteger($VariableProperty);
            if ($id > 0) {
                $this->RegisterReference($id);
            }
        }

        // Aktualisiere registrierte Nachrichten
        foreach ($this->GetMessageList() as $senderID => $messageIDs) {
            foreach ($messageIDs as $messageID) {
                $this->UnregisterMessage($senderID, $messageID);
            }
        }
        foreach (self::WATCHED_PROPERTIES as $VariableProperty) {
            $id = $this->ReadPropertyInteger($VariableProperty);
            if ($id > 0) {
                $this->RegisterMessage($id, VM_UPDATE);
            }
        }

        // Schicke eine komplette Update-Nachricht an die Darstellung, da sich ja Parameter geändert haben können
        $this->UpdateVisualizationValue($this->GetFullUpdateMessage());
    }

    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void
    {
        if ($Message === IPS_KERNELSTARTED) {
            $this->ApplyChanges();
            return;
        }
        if ($Message !== VM_UPDATE) {
            return;
        }

        // Kein Abbruch nach dem ersten Treffer: steht dieselbe Variable in mehreren Schaltern, folgen alle.
        // $result sammelt dabei wie bisher über die Treffer hinweg (die zweite Nachricht wiederholt die erste).
        $result = [];
        foreach (self::WATCHED_PROPERTIES as $VariableProperty) {
            $variableID = $this->ReadPropertyInteger($VariableProperty);
            if ($SenderID !== $variableID) {
                continue;
            }

            // Teile der HTML-Darstellung den neuen Wert mit. Damit dieser korrekt formatiert ist, holen wir uns den von der Variablen via GetValueFormatted
            $this->UpdateVisualizationValue($this->EncodeJSON([$VariableProperty => GetValueFormatted($variableID)]));

            //Icon und Farbe abrufen
            $result[$VariableProperty . 'Color'] = $this->GetColor($variableID);

            if ($VariableProperty !== 'bgImage') {
                if ($this->ReadPropertyBoolean($VariableProperty . 'NameSwitch')) {
                    $result[$VariableProperty . 'name'] = IPS_GetName($variableID);
                }
                if ($this->ReadPropertyBoolean($VariableProperty . 'IconSwitch')) {
                    $icon = $this->GetIcon($variableID, $this->ReadPropertyBoolean($VariableProperty . 'VarIconSwitch'));
                    if ($icon !== 'Transparent') {
                        $result[$VariableProperty . 'icon'] = $icon;
                    }
                }
                if ($this->ReadPropertyBoolean($VariableProperty . 'AssoSwitch')) {
                    $result[$VariableProperty . 'asso'] = $this->CheckAndGetValueFormatted($VariableProperty);
                }
                $result[$VariableProperty . 'AltName'] = $this->ReadPropertyString($VariableProperty . 'AltName');
            }

            $this->UpdateVisualizationValue($this->EncodeJSON($result));
        }
    }


    public function RequestAction(string $Ident, mixed $Value): void
    {
        // Nachrichten von der HTML-Darstellung schicken immer den Ident passend zur Eigenschaft; der Wert wird nicht
        // gebraucht, die Variable wird umgeschaltet. Andere Idents werden an der Systemgrenze abgewiesen.
        if (!in_array($Ident, self::SWITCHES, true)) {
            throw new Exception('Invalid ident: ' . $Ident);
        }
        $variableID = $this->ReadPropertyInteger($Ident);
        if (!IPS_VariableExists($variableID)) {
            $this->SendDebug('Error in RequestAction', 'Variable to be updated does not exist', 0);
            return;
        }
        // Umschalten des Werts der Variable
        $currentValue = GetValue($variableID);
        RequestAction($variableID, !$currentValue);
    }


    public function GetVisualizationTile(): string
    {
        // Füge statisches HTML aus Datei hinzu
        $module = file_get_contents(__DIR__ . '/module.html');
        if ($module === false) {
            $this->LogMessage('module.html could not be loaded', KL_ERROR);
            return '';
        }

        // Füge ein Skript hinzu, um beim Laden, analog zu Änderungen bei Laufzeit, die Werte zu setzen.
        // Das doppelte Kodieren ist beabsichtigt: es liefert die Nachricht als JS-Stringliteral.
        $initialHandling = '<script>handleMessage(' . $this->EncodeJSON($this->GetFullUpdateMessage()) . ')</script>';

        // Gebe alles zurück.
        // Wichtig: $initialHandling nach hinten, da die Funktion handleMessage erst im HTML definiert wird
        return $module . $initialHandling;
    }



    // Generiere eine Nachricht, die alle Elemente in der HTML-Darstellung aktualisiert.
    // Die Reihenfolge der Schlüssel ist Teil des Vertrags mit handleMessage (z. B. altname nach name).
    private function GetFullUpdateMessage(): string
    {
        $result = [];

        for ($i = 1; $i <= 10; $i++) {
            $schalterID = $this->ReadPropertyInteger("Schalter$i");
            if (IPS_VariableExists($schalterID)) {
                $prefix = "schalter$i";
                $result[$prefix] = $this->CheckAndGetValueFormatted("Schalter$i");
                $result[$prefix . 'breite'] = $this->ReadPropertyFloat("Schalter{$i}Breite");
                $result[$prefix . 'color'] = $this->GetColor($schalterID);

                if ($this->ReadPropertyBoolean("Schalter{$i}NameSwitch")) {
                    $result[$prefix . 'name'] = IPS_GetName($schalterID);
                }

                $iconSwitch = $this->ReadPropertyBoolean("Schalter{$i}IconSwitch");
                $varIconSwitch = $this->ReadPropertyBoolean("Schalter{$i}VarIconSwitch");
                if ($iconSwitch) {
                    $icon = $this->GetIcon($schalterID, $varIconSwitch);
                    if ($icon !== "Transparent") {
                        $result[$prefix . 'icon'] = $icon;
                    }
                }

                if ($this->ReadPropertyBoolean("Schalter{$i}AssoSwitch")) {
                    $result[$prefix . 'asso'] = $this->CheckAndGetValueFormatted("Schalter$i");
                }
            }
        }

        // sprintf('%06X', -1) ergibt #FFFFFFFFFFFFFFFF für „transparent“ – bleibt wie bisher
        $result['hintergrundfarbe'] = '#' . sprintf('%06X', $this->ReadPropertyInteger('Kachelhintergrundfarbe'));
        $result['infomenueschriftfarbe'] = '#' . sprintf('%06X', $this->ReadPropertyInteger('InfoMenueSchriftfarbe'));
        $result['schriftgroesse'] = $this->ReadPropertyFloat('Schriftgroesse');
        $result['transparenz'] = $this->ReadPropertyFloat('Bildtransparenz');
        for ($i = 1; $i <= 10; $i++) {
            $result["schalter{$i}altname"] = $this->ReadPropertyString("Schalter{$i}AltName");
        }

        // Prüfe vorweg, ob ein Bild ausgewählt wurde
        $imageID = $this->ReadPropertyInteger('bgImage');
        if (IPS_MediaExists($imageID)) {
            $image = IPS_GetMedia($imageID);
            if ($image['MediaType'] === MEDIATYPE_IMAGE) {
                // Falls ja, ermittle den Anfang der src basierend auf dem Dateitypen; ohne bekannten Typ ist das
                // Bild kein unterstützter Dateityp. IPS_GetMediaContent liefert den Inhalt bereits base64-codiert
                $imageFile = explode('.', $image['MediaFile']);
                $mime = self::IMAGE_TYPES[end($imageFile)] ?? '';
                if ($mime !== '') {
                    $result['bgimage'] = 'data:' . $mime . ';base64,' . IPS_GetMediaContent($imageID);
                }
            }
        } else {
            $imageContent = 'data:image/png;base64,' . base64_encode((string) file_get_contents(self::DEFAULT_BACKGROUND));
            if ($this->ReadPropertyBoolean('BG_Off')) {
                $result['bgimage'] = $imageContent;
            }
        }

        return $this->EncodeJSON($result);
    }

    // Wie bisher json_encode mit Standard-Flags (maskierte Schrägstriche halten Namen aus dem Skript-Tag heraus);
    // ungültiges UTF-8 wird ersetzt, statt die ganze Nachricht zu verlieren, und das Ergebnis ist immer ein String.
    private function EncodeJSON(mixed $data): string
    {
        try {
            return json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->LogMessage('Visualization JSON: ' . $e->getMessage(), KL_ERROR);
            return '{}';
        }
    }

    // false, wenn die Variable fehlt – wie bisher
    private function CheckAndGetValueFormatted(string $property): string|false
    {
        $id = $this->ReadPropertyInteger($property);
        if (IPS_VariableExists($id)) {
            return GetValueFormatted($id);
        }
        return false;
    }


    private function GetColor(int $id): string
    {
        $variable = IPS_GetVariable($id);
        $Value = GetValue($id);
        $profile = $variable['VariableCustomProfile'] ?: $variable['VariableProfile'];

        if ($profile && IPS_VariableProfileExists($profile)) {
            $p = IPS_GetVariableProfile($profile);

            foreach ($p['Associations'] as $association) {
                // Bewusst lose verglichen: Assoziationswerte sind Floats, der Wert kann Boolean oder Integer sein
                if (isset($association['Value'], $association['Color']) && $association['Value'] == $Value) {
                    return $association['Color'] === -1 ? "" : sprintf('%06X', $association['Color']);
                }
            }
        }
        return "";
    }


    private function GetIcon(int $id, bool $varicon): string
    {
        $variable = IPS_GetVariable($id);
        $Value = GetValue($id);
        //Abfragen ob das Variablen-Icon oder das Profil-Icon verwendet werden soll
        if ($varicon) {
            $object = IPS_GetObject($id);
            return $object['ObjectIcon'] !== '' ? $object['ObjectIcon'] : 'Transparent';
        }

        // Profil-Icon abrufen
        $profile = $variable['VariableCustomProfile'] ?: $variable['VariableProfile'];
        $icon = '';

        if ($profile && IPS_VariableProfileExists($profile)) {
            $p = IPS_GetVariableProfile($profile);

            foreach ($p['Associations'] as $association) {
                // Bewusst lose verglichen, siehe GetColor
                if (isset($association['Value']) && $association['Icon'] !== '' && $association['Value'] == $Value) {
                    $icon = $association['Icon'];
                    break;
                }
            }

            if ($icon === '' && isset($p['Icon']) && $p['Icon'] !== '') {
                $icon = $p['Icon'];
            }
        }

        return $icon === '' ? 'Transparent' : $icon;
    }

}
