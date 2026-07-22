<?php
declare(strict_types=1);

final class SaturnAtlas
{
    public static function houses(): array
    {
        return [

            1 => ['priority'=>100,'rating'=>1,'themes'=>['salute'=>100,'fatica'=>100,'isolamento'=>80]],
            2 => ['priority'=>90,'rating'=>2,'themes'=>['denaro'=>90,'restrizioni'=>100]],
            3 => ['priority'=>80,'rating'=>2,'themes'=>['studio'=>60,'spostamenti'=>80,'parenti'=>70]],
            4 => ['priority'=>95,'rating'=>1,'themes'=>['casa'=>90,'famiglia'=>100]],
            5 => ['priority'=>85,'rating'=>2,'themes'=>['amore'=>80,'figli'=>90]],
            6 => ['priority'=>100,'rating'=>1,'themes'=>['salute'=>100,'lavoro'=>95,'responsabilita'=>90]],
            7 => ['priority'=>100,'rating'=>1,'themes'=>['relazioni'=>100,'matrimonio'=>90,'separazioni'=>80]],
            8 => ['priority'=>90,'rating'=>2,'themes'=>['prove'=>95,'trasformazione'=>90]],
            9 => ['priority'=>80,'rating'=>2,'themes'=>['estero'=>70,'studio'=>70]],
            10=> ['priority'=>100,'rating'=>2,'themes'=>['carriera'=>95,'responsabilita'=>100,'prestigio'=>70]],
            11=> ['priority'=>80,'rating'=>2,'themes'=>['amicizie'=>80,'progetti'=>70]],
            12=> ['priority'=>100,'rating'=>1,'themes'=>['prove'=>100,'isolamento'=>95,'spiritualita'=>70]],

        ];
    }
}
