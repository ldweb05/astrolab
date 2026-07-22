<?php
declare(strict_types=1);

final class VenusAtlas
{
    public static function houses(): array
    {
        return [

            1 => ['priority'=>90,'rating'=>5,'themes'=>['identita'=>90,'salute'=>70,'relazioni'=>80]],
            2 => ['priority'=>85,'rating'=>4,'themes'=>['denaro'=>90,'patrimonio'=>80]],
            3 => ['priority'=>80,'rating'=>4,'themes'=>['comunicazione'=>90,'studio'=>70]],
            4 => ['priority'=>90,'rating'=>5,'themes'=>['casa'=>95,'famiglia'=>90]],
            5 => ['priority'=>100,'rating'=>5,'themes'=>['amore'=>100,'figli'=>90,'creativita'=>100]],
            6 => ['priority'=>70,'rating'=>3,'themes'=>['lavoro'=>70,'salute'=>75]],
            7 => ['priority'=>100,'rating'=>5,'themes'=>['relazioni'=>100,'matrimonio'=>100,'societa'=>90]],
            8 => ['priority'=>70,'rating'=>3,'themes'=>['trasformazione'=>70,'denaro'=>60]],
            9 => ['priority'=>85,'rating'=>4,'themes'=>['viaggi'=>85,'estero'=>80,'studio'=>70]],
            10=> ['priority'=>95,'rating'=>5,'themes'=>['carriera'=>95,'prestigio'=>90]],
            11=> ['priority'=>90,'rating'=>5,'themes'=>['amicizie'=>95,'progetti'=>90]],
            12=> ['priority'=>60,'rating'=>3,'themes'=>['spiritualita'=>60,'prove'=>40]],

        ];
    }
}
