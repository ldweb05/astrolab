<?php
declare(strict_types=1);

final class JupiterAtlas
{
    public static function houses(): array
    {
        return [

            1 => ['priority'=>90,'rating'=>5,'themes'=>['salute'=>90,'identita'=>80,'iniziative'=>70]],
            2 => ['priority'=>90,'rating'=>5,'themes'=>['denaro'=>100,'patrimonio'=>90]],
            3 => ['priority'=>75,'rating'=>4,'themes'=>['studio'=>80,'viaggi'=>70,'comunicazione'=>75]],
            4 => ['priority'=>85,'rating'=>4,'themes'=>['casa'=>95,'famiglia'=>90]],
            5 => ['priority'=>95,'rating'=>5,'themes'=>['amore'=>95,'figli'=>90,'creativita'=>95]],
            6 => ['priority'=>85,'rating'=>4,'themes'=>['lavoro'=>90,'salute'=>80]],
            7 => ['priority'=>90,'rating'=>5,'themes'=>['relazioni'=>100,'matrimonio'=>95]],
            8 => ['priority'=>70,'rating'=>3,'themes'=>['trasformazione'=>80,'denaro'=>60]],
            9 => ['priority'=>95,'rating'=>5,'themes'=>['viaggi'=>100,'estero'=>95,'studio'=>90]],
            10=> ['priority'=>100,'rating'=>5,'themes'=>['carriera'=>100,'successo'=>95,'prestigio'=>90]],
            11=> ['priority'=>90,'rating'=>5,'themes'=>['amicizie'=>95,'progetti'=>100]],
            12=> ['priority'=>60,'rating'=>3,'themes'=>['spiritualita'=>80,'protezione'=>60]],

        ];
    }
}
