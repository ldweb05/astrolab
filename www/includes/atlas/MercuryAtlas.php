<?php
declare(strict_types=1);

final class MercuryAtlas
{
    public static function houses(): array
    {
        return [

            1 => ['priority'=>70,'rating'=>4,'themes'=>['comunicazione'=>90,'identita'=>70]],
            2 => ['priority'=>80,'rating'=>4,'themes'=>['denaro'=>80,'affari'=>90]],
            3 => ['priority'=>100,'rating'=>5,'themes'=>['studio'=>100,'comunicazione'=>100,'spostamenti'=>90]],
            4 => ['priority'=>70,'rating'=>3,'themes'=>['casa'=>70,'famiglia'=>60]],
            5 => ['priority'=>80,'rating'=>4,'themes'=>['creativita'=>90,'figli'=>70]],
            6 => ['priority'=>90,'rating'=>5,'themes'=>['lavoro'=>100,'salute'=>70]],
            7 => ['priority'=>85,'rating'=>4,'themes'=>['relazioni'=>90,'contratti'=>100]],
            8 => ['priority'=>70,'rating'=>3,'themes'=>['trasformazione'=>60,'denaro'=>60]],
            9 => ['priority'=>90,'rating'=>5,'themes'=>['studio'=>90,'estero'=>80,'viaggi'=>80]],
            10=> ['priority'=>90,'rating'=>5,'themes'=>['carriera'=>90,'successo'=>80]],
            11=> ['priority'=>85,'rating'=>4,'themes'=>['amicizie'=>80,'progetti'=>90]],
            12=> ['priority'=>60,'rating'=>2,'themes'=>['introspezione'=>70]],

        ];
    }
}
