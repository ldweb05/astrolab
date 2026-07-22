<?php
declare(strict_types=1);

final class PlutoAtlas
{
    public static function houses(): array
    {
        return [

            1 => ['priority'=>90,'rating'=>3,'themes'=>['trasformazione'=>100,'identita'=>90]],
            2 => ['priority'=>80,'rating'=>3,'themes'=>['denaro'=>80,'patrimonio'=>90]],
            3 => ['priority'=>75,'rating'=>3,'themes'=>['comunicazione'=>70,'studio'=>70]],
            4 => ['priority'=>90,'rating'=>3,'themes'=>['casa'=>100,'famiglia'=>90]],
            5 => ['priority'=>85,'rating'=>3,'themes'=>['amore'=>80,'figli'=>80,'creativita'=>90]],
            6 => ['priority'=>90,'rating'=>2,'themes'=>['lavoro'=>90,'salute'=>80]],
            7 => ['priority'=>95,'rating'=>2,'themes'=>['relazioni'=>100,'trasformazione'=>100]],
            8 => ['priority'=>100,'rating'=>5,'themes'=>['trasformazione'=>100,'prove'=>100,'rigenerazione'=>100]],
            9 => ['priority'=>80,'rating'=>3,'themes'=>['estero'=>70,'studio'=>80]],
            10=> ['priority'=>95,'rating'=>4,'themes'=>['carriera'=>100,'potere'=>95,'prestigio'=>90]],
            11=> ['priority'=>80,'rating'=>3,'themes'=>['amicizie'=>80,'progetti'=>80]],
            12=> ['priority'=>90,'rating'=>3,'themes'=>['prove'=>90,'spiritualita'=>80,'introspezione'=>90]],

        ];
    }
}
