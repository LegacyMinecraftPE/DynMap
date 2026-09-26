<?php

/*
__PocketMine Plugin__
name=DynMap
version=1.0.0
description=DynMap plugin for NostalgiaCore
author=colbux
class=DynMap
apiversion=12,12.1,12.2
*/

class DynMap implements Plugin {

    private $api, $server;
    private $outputDir;
    private $updateInterval = 300;
    private $lastMapData = "";
    private $lastMapTime = 0;

    private static $transparentBlocks = array(
        0 => true, 6 => true, 8 => true, 9 => true, 10 => true, 11 => true, 18 => true, 20 => true,
        26 => true, 27 => true, 28 => true, 30 => true, 31 => true, 32 => true,
        37 => true, 38 => true, 39 => true, 40 => true, 44 => true,
        50 => true, 51 => true, 59 => true, 63 => true, 64 => true, 65 => true, 66 => true,
        68 => true, 71 => true, 75 => true, 76 => true, 78 => true,
        79 => true, 83 => true, 85 => true, 92 => true, 96 => true,
        101 => true, 102 => true, 104 => true, 105 => true, 106 => true,
        107 => true, 113 => true, 126 => true, 139 => true, 141 => true, 142 => true,
        157 => true, 158 => true, 171 => true, 244 => true
    );

    private static $blockColors = array(
        1 => array(120, 120, 120),
                                        2 => array(118, 172, 88),
                                        3 => array(134, 96, 67),
                                        4 => array(105, 105, 105),
                                        5 => array(157, 128, 79),
                                        6 => array(80, 140, 40),
                                        7 => array(50, 50, 50),
                                        8 => array(65, 105, 225),
                                        9 => array(65, 105, 225),
                                        10=> array(255, 80, 0),
                                        11=> array(255, 80, 0),
                                        12=> array(219, 211, 160),
                                        13=> array(136, 126, 126),
                                        14=> array(143, 140, 125),
                                        15=> array(136, 130, 127),
                                        16=> array(115, 115, 115),
                                        17=> array(104, 83, 50),
                                        18=> array(70, 130, 40),
                                        19=> array(195, 195, 75),
                                        20=> array(230, 240, 255),
                                        21=> array(80, 110, 150),
                                        22=> array(40, 75, 160),
                                        24=> array(219, 211, 160),
                                        26=> array(200, 50, 50),
                                        27=> array(150, 110, 80),
                                        30=> array(230, 230, 230),
                                        35=> array(220, 220, 220),
                                        37=> array(255, 230, 0),
                                        38=> array(0, 120, 255),
                                        39=> array(180, 140, 100),
                                        40=> array(200, 50, 50),
                                        41=> array(250, 238, 77),
                                        42=> array(220, 220, 220),
                                        43=> array(150, 150, 150),
                                        44=> array(150, 150, 150),
                                        45=> array(170, 86, 62),
                                        46=> array(200, 80, 80),
                                        47=> array(110, 85, 55),
                                        48=> array(100, 130, 100),
                                        49=> array(40, 30, 50),
                                        50=> array(255, 200, 0),
                                        51=> array(255, 200, 0),
                                        52=> array(40, 60, 80),
                                        53=> array(157, 128, 79),
                                        54=> array(125, 90, 40),
                                        56=> array(120, 170, 170),
                                        57=> array(100, 220, 220),
                                        58=> array(110, 80, 50),
                                        59=> array(140, 180, 50),
                                        60=> array(90, 60, 40),
                                        61=> array(110, 110, 110),
                                        62=> array(110, 110, 110),
                                        64=> array(130, 100, 50),
                                        65=> array(130, 100, 50),
                                        66=> array(150, 110, 80),
                                        67=> array(105, 105, 105),
                                        71=> array(200, 200, 200),
                                        73=> array(150, 100, 100),
                                        74=> array(150, 100, 100),
                                        75=> array(200, 0, 0),
                                        76=> array(255, 0, 0),
                                        78=> array(240, 255, 255),
                                        79=> array(140, 185, 255),
                                        80=> array(240, 255, 255),
                                        81=> array(40, 120, 40),
                                        82=> array(160, 160, 180),
                                        83=> array(80, 200, 80),
                                        85=> array(157, 128, 79),
                                        86=> array(220, 120, 40),
                                        87=> array(130, 60, 60),
                                        89=> array(250, 210, 120),
                                        91=> array(220, 120, 40),
                                        92=> array(240, 200, 200),
                                        96=> array(130, 100, 50),
                                        98=> array(115, 115, 115),
                                        102=>array(230, 240, 255),
                                        103=>array(140, 180, 80),
                                        107=>array(157, 128, 79),
                                        108=>array(170, 86, 62),
                                        109=>array(115, 115, 115),
                                        112=>array(70, 40, 50),
                                        114=>array(70, 40, 50),
                                        128=>array(219, 211, 160),
                                        134=>array(104, 83, 50),
                                        135=>array(190, 170, 130),
                                        136=>array(140, 100, 70),
                                        155=>array(230, 225, 220),
                                        156=>array(230, 225, 220),
                                        158=>array(157, 128, 79),
                                        170=>array(200, 180, 50),
                                        173=>array(30, 30, 30),
                                        244=>array(140, 60, 60),
                                        245=>array(120, 120, 120),
                                        246=>array(250, 50, 50),
                                        247=>array(100, 200, 250)
    );

    public function __construct(ServerAPI $api, $server = false) {
        $this->api = $api;
        $this->server = ServerAPI::request();
    }

    public function init() {
        $this->outputDir = "/var/www/html/map/";
        if (!is_dir($this->outputDir)) {
            @mkdir($this->outputDir, 0755, true);
        }

        $this->configPath = $this->api->plugin->configPath($this);
        $this->config = new Config($this->configPath . "config.yml", CONFIG_YAML, array(
            "worlds" => array("world")
        ));
        $this->worlds = $this->config->get("worlds");
        if (!is_array($this->worlds)) $this->worlds = array("world");

        $this->lastMapTimes = array();
        $this->lastMapDatas = array();
        foreach ($this->worlds as $w) {
            $this->lastMapTimes[$w] = 0;
            $this->lastMapDatas[$w] = "";
        }

        $this->api->schedule($this->updateInterval, array($this, "update"), array(), true);
    }

    public function update() {
        file_put_contents($this->outputDir . "worlds.json", json_encode(array_values($this->worlds)));
        foreach ($this->worlds as $wName) {
            $level = $this->api->level->get($wName);
            if (!$level) continue;

            $pmfLevel = $level->level;
            $players = array();

            foreach ($this->server->clients as $client) {
                if ($client instanceof Player && isset($client->username) && $client->level === $level) {
                    $ex = ($client->entity !== null) ? $client->entity->x : $client->x;
                    $ey = ($client->entity !== null) ? $client->entity->y : $client->y;
                    $ez = ($client->entity !== null) ? $client->entity->z : $client->z;

                    $players[] = array(
                        "name" => $client->username,
                        "x"    => round($ex, 1),
                                       "y"    => round($ey, 1),
                                       "z"    => round($ez, 1),
                    );
                }
            }

            $now = time();
            if ($this->lastMapTimes[$wName] === 0 || ($now - $this->lastMapTimes[$wName]) >= 120) {
                $loadedChunks = array();
                for ($cX = 0; $cX < 16; $cX++) {
                    for ($cZ = 0; $cZ < 16; $cZ++) {
                        $pmfLevel->loadChunk($cX, $cZ);
                        $index = $pmfLevel->getIndex($cX, $cZ);
                        if (isset($pmfLevel->chunks[$index]) && $pmfLevel->chunks[$index] !== false) {
                            $loadedChunks[$cX][$cZ] = $pmfLevel->chunks[$index];
                        }
                    }
                }

                $heightMap = array_fill(0, 256, array_fill(0, 256, 0));
                $highestMap = array_fill(0, 256, array_fill(0, 256, 0));
                $tb = self::$transparentBlocks;

                for ($cX = 0; $cX < 16; $cX++) {
                    for ($cZ = 0; $cZ < 16; $cZ++) {
                        if (!isset($loadedChunks[$cX][$cZ])) continue;
                        $ch = $loadedChunks[$cX][$cZ];
                        $startX = $cX << 4;
                        $startZ = $cZ << 4;

                        for ($localX = 0; $localX < 16; $localX++) {
                            for ($localZ = 0; $localZ < 16; $localZ++) {
                                $x = $startX + $localX;
                                $z = $startZ + $localZ;

                                $hasHighest = false;
                                $hasHeight = false;

                                for ($cY = 7; $cY >= 0; $cY--) {
                                    if (!isset($ch[$cY]) || $ch[$cY] === false) continue;
                                    $mini = $ch[$cY];
                                    for ($localY = 15; $localY >= 0; $localY--) {
                                        $offset = $localY + ($localX << 5) + ($localZ << 9);
                                        $id = ord($mini[$offset]);
                                        if ($id === 0) continue;

                                        if (!$hasHighest) {
                                            $highestMap[$x][$z] = ($cY << 4) + $localY;
                                            $hasHighest = true;
                                        }

                                        if (!$hasHeight) {
                                            if ($id !== 6 && $id !== 27 && $id !== 28 && $id !== 30 && $id !== 31 && $id !== 32 && $id !== 37 && $id !== 38 && $id !== 50 && $id !== 51 && $id !== 55 && $id !== 59 && $id !== 63 && $id !== 66 && $id !== 68 && $id !== 75 && $id !== 76 && $id !== 83 && $id !== 104 && $id !== 105 && $id !== 157) {
                                                $heightMap[$x][$z] = ($cY << 4) + $localY;
                                                $hasHeight = true;
                                            }
                                        }

                                        if ($hasHighest && $hasHeight) {
                                            break 2;
                                        }
                                    }
                                }

                                if ($heightMap[$x][$z] === 0) {
                                    $heightMap[$x][$z] = $highestMap[$x][$z];
                                }
                            }
                        }
                    }
                }

                $binData = "";

                for ($cX = 0; $cX < 16; $cX++) {
                    for ($cZ = 0; $cZ < 16; $cZ++) {
                        if (!isset($loadedChunks[$cX][$cZ])) continue;
                        $ch = $loadedChunks[$cX][$cZ];
                        $startX = $cX << 4;
                        $startZ = $cZ << 4;

                        for ($localX = 0; $localX < 16; $localX++) {
                            for ($localZ = 0; $localZ < 16; $localZ++) {
                                $x = $startX + $localX;
                                $z = $startZ + $localZ;

                                $startScanY = $highestMap[$x][$z];
                                if ($startScanY == 0) continue;

                                $terrainY = $heightMap[$x][$z];
                                for ($y = $startScanY; $y >= 0; $y--) {
                                    $cY = $y >> 4;
                                    $localY = $y & 0x0f;
                                    if (!isset($ch[$cY]) || $ch[$cY] === false) continue;
                                    $mini = $ch[$cY];
                                    $offset = $localY + ($localX << 5) + ($localZ << 9);
                                    $id = ord($mini[$offset]);

                                    if ($id === 0) continue;

                                    $visible = false;

                                    $aboveId = 0;
                                    $aboveY = $y + 1;
                                    if ($aboveY < 128) {
                                        $aboveCY = $aboveY >> 4;
                                        if (isset($ch[$aboveCY]) && $ch[$aboveCY] !== false) {
                                            $aboveOffset = ($aboveY & 15) + ($localX << 5) + ($localZ << 9);
                                            $aboveId = ord($ch[$aboveCY][$aboveOffset]);
                                        }
                                    }
                                    if ($aboveY === 128 || $aboveId === 0 || isset($tb[$aboveId])) {
                                        $visible = true;
                                    }

                                    if (!$visible && $z < 255) {
                                        $flId = 0;
                                        $flCX = $x >> 4;
                                        $flCZ = ($z + 1) >> 4;
                                        if (isset($loadedChunks[$flCX][$flCZ])) {
                                            $flCh = $loadedChunks[$flCX][$flCZ];
                                            $flCY = $y >> 4;
                                            if (isset($flCh[$flCY]) && $flCh[$flCY] !== false) {
                                                $flOffset = ($y & 15) + (($x & 15) << 5) + ((($z + 1) & 15) << 9);
                                                $flId = ord($flCh[$flCY][$flOffset]);
                                            }
                                        }
                                        if ($flId === 0 || isset($tb[$flId])) {
                                            $visible = true;
                                        }
                                    }
                                    if (!$visible && $z >= 255) {
                                        $visible = true;
                                    }

                                    if (!$visible && $x < 255) {
                                        $frId = 0;
                                        $frCX = ($x + 1) >> 4;
                                        $frCZ = $z >> 4;
                                        if (isset($loadedChunks[$frCX][$frCZ])) {
                                            $frCh = $loadedChunks[$frCX][$frCZ];
                                            $frCY = $y >> 4;
                                            if (isset($frCh[$frCY]) && $frCh[$frCY] !== false) {
                                                $frOffset = ($y & 15) + ((($x + 1) & 15) << 5) + (($z & 15) << 9);
                                                $frId = ord($frCh[$frCY][$frOffset]);
                                            }
                                        }
                                        if ($frId === 0 || isset($tb[$frId])) {
                                            $visible = true;
                                        }
                                    }
                                    if (!$visible && $x >= 255) {
                                        $visible = true;
                                    }

                                    if (!$visible) continue;

                                    $metaIndex = ($localY >> 1) + 16 + ($localX << 5) + ($localZ << 9);
                                    $dataByte = ord($mini[$metaIndex]);
                                    $meta = ($y & 1) ? (($dataByte >> 4) & 0x0f) : ($dataByte & 0x0f);

                                    if ($id === 2) {
                                        if ($aboveId === 78 || $aboveId === 80) {
                                            $meta = 1;
                                        }
                                    }

                                    if ($y >= $terrainY) {
                                        $prevY = ($z > 0) ? $heightMap[$x][$z - 1] : $terrainY;
                                        if ($terrainY > $prevY) $shadeVal = 1;
                                        elseif ($terrainY < $prevY) $shadeVal = 2;
                                        else $shadeVal = 0;
                                    } else {
                                        $shadeVal = 3;
                                    }

                                    $packed = ($shadeVal << 4) | $meta;
                                    $binData .= chr($x) . chr($z) . chr($y) . chr($id) . chr($packed);
                                }
                            }
                        }
                    }
                }
                $this->lastMapDatas[$wName] = base64_encode($binData);
                $this->lastMapTimes[$wName] = $now;

                $data = [
                    "lv" => $level->getName(),
                    "mp" => $this->lastMapDatas[$wName]
                ];
                file_put_contents($this->outputDir . "mapdata_{$wName}.json", json_encode($data));
            }

            $playerData = [
                "pl" => $players
            ];
            file_put_contents($this->outputDir . "players_{$wName}.json", json_encode($playerData));
        }
    }

    public function __destruct() {}
}
