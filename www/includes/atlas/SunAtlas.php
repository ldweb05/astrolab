<?php
declare(strict_types=1);

final class SunAtlas
{
    public static function houses(): array
    {
        return [

            1 => ['priority'=>90,'rating'=>5,'themes'=>['identita'=>100,'salute'=>80,'iniziative'=>80]],
            2 => ['priority'=>80,'rating'=>4,'themes'=>['denaro'=>85,'patrimonio'=>75]],
            3 => ['priority'=>75,'rating'=>4,'themes'=>['studio'=>80,'comunicazione'=>80]],
            4 => ['priority'=>90,'rating'=>5,'themes'=>['casa'=>90,'famiglia'=>95]],
            5 => ['priority'=>95,'rating'=>5,'themes'=>['amore'=>90,'figli'=>95,'creativita'=>100]],
            6 => ['priority'=>80,'rating'=>4,'themes'=>['lavoro'=>90,'salute'=>85]],
            7 => ['priority'=>90,'rating'=>5,'themes'=>['relazioni'=>95,'matrimonio'=>90]],
            8 => ['priority'=>70,'rating'=>3,'themes'=>['trasformazione'=>85,'prove'=>70]],
            9 => ['priority'=>90,'rating'=>5,'themes'=>['viaggi'=>90,'estero'=>95,'studio'=>85]],
            10=> ['priority'=>100,'rating'=>5,'themes'=>['carriera'=>100,'prestigio'=>95,'successo'=>95]],
            11=> ['priority'=>85,'rating'=>4,'themes'=>['amicizie'=>90,'progetti'=>95]],
            12=> ['priority'=>65,'rating'=>3,'themes'=>['spiritualita'=>80,'introspezione'=>85]],

        ];
    }
}
