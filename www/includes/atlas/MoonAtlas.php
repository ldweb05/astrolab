<?php
declare(strict_types=1);

final class MoonAtlas
{
    public static function houses(): array
    {
        return [

            1 => ['priority'=>85,'rating'=>4,'themes'=>['identita'=>80,'salute'=>75,'emotivita'=>95]],
            2 => ['priority'=>85,'rating'=>4,'themes'=>['denaro'=>90,'entrate'=>85]],
            3 => ['priority'=>80,'rating'=>4,'themes'=>['comunicazione'=>90,'spostamenti'=>80]],
            4 => ['priority'=>95,'rating'=>5,'themes'=>['casa'=>100,'famiglia'=>100]],
            5 => ['priority'=>95,'rating'=>5,'themes'=>['amore'=>90,'figli'=>100,'creativita'=>85]],
            6 => ['priority'=>80,'rating'=>3,'themes'=>['salute'=>85,'lavoro'=>80]],
            7 => ['priority'=>90,'rating'=>5,'themes'=>['relazioni'=>95,'matrimonio'=>90]],
            8 => ['priority'=>75,'rating'=>3,'themes'=>['trasformazione'=>80,'prove'=>70]],
            9 => ['priority'=>85,'rating'=>4,'themes'=>['viaggi'=>90,'estero'=>80]],
            10=> ['priority'=>90,'rating'=>5,'themes'=>['carriera'=>90,'popolarita'=>95]],
            11=> ['priority'=>85,'rating'=>4,'themes'=>['amicizie'=>95,'progetti'=>85]],
            12=> ['priority'=>70,'rating'=>3,'themes'=>['introspezione'=>90,'spiritualita'=>80]],

        ];
    }
}
