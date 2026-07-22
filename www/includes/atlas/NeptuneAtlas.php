<?php
declare(strict_types=1);

final class NeptuneAtlas
{
    public static function houses(): array
    {
        return [

            1 => ['priority'=>75,'rating'=>3,'themes'=>['spiritualita'=>90,'identita'=>70]],
            2 => ['priority'=>70,'rating'=>2,'themes'=>['denaro'=>60,'confusione'=>90]],
            3 => ['priority'=>70,'rating'=>3,'themes'=>['studio'=>70,'intuizione'=>90]],
            4 => ['priority'=>80,'rating'=>3,'themes'=>['casa'=>80,'famiglia'=>70]],
            5 => ['priority'=>85,'rating'=>4,'themes'=>['amore'=>90,'creativita'=>100]],
            6 => ['priority'=>80,'rating'=>2,'themes'=>['salute'=>80,'lavoro'=>70]],
            7 => ['priority'=>85,'rating'=>3,'themes'=>['relazioni'=>90,'idealizzazione'=>90]],
            8 => ['priority'=>80,'rating'=>3,'themes'=>['trasformazione'=>80,'psicologia'=>90]],
            9 => ['priority'=>90,'rating'=>4,'themes'=>['estero'=>80,'spiritualita'=>100]],
            10=> ['priority'=>80,'rating'=>3,'themes'=>['carriera'=>70,'ispirazione'=>90]],
            11=> ['priority'=>80,'rating'=>4,'themes'=>['amicizie'=>80,'progetti'=>80]],
            12=> ['priority'=>100,'rating'=>5,'themes'=>['spiritualita'=>100,'prove'=>80,'introspezione'=>100]],

        ];
    }
}
