<?php
declare(strict_types=1);

final class UranusAtlas
{
    public static function houses(): array
    {
        return [

            1 => ['priority'=>85,'rating'=>3,'themes'=>['cambiamenti'=>100,'identita'=>80]],
            2 => ['priority'=>80,'rating'=>3,'themes'=>['denaro'=>80,'imprevisti'=>90]],
            3 => ['priority'=>85,'rating'=>4,'themes'=>['studio'=>80,'comunicazione'=>90,'spostamenti'=>90]],
            4 => ['priority'=>90,'rating'=>3,'themes'=>['casa'=>100,'traslochi'=>95]],
            5 => ['priority'=>85,'rating'=>4,'themes'=>['amore'=>90,'creativita'=>95]],
            6 => ['priority'=>90,'rating'=>3,'themes'=>['lavoro'=>100,'salute'=>70]],
            7 => ['priority'=>95,'rating'=>2,'themes'=>['relazioni'=>100,'rotture'=>95]],
            8 => ['priority'=>80,'rating'=>3,'themes'=>['trasformazione'=>90]],
            9 => ['priority'=>90,'rating'=>4,'themes'=>['viaggi'=>90,'estero'=>90]],
            10=> ['priority'=>100,'rating'=>4,'themes'=>['carriera'=>100,'innovazione'=>90]],
            11=> ['priority'=>90,'rating'=>5,'themes'=>['amicizie'=>100,'progetti'=>95]],
            12=> ['priority'=>75,'rating'=>2,'themes'=>['prove'=>80,'liberazione'=>70]],

        ];
    }
}
