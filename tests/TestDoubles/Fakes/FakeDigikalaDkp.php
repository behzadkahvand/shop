<?php

namespace App\Tests\TestDoubles\Fakes;

class FakeDigikalaDkp
{
    public static function build(): array
    {
        return [
            'product' => [
                "title_fa" => "dummy product",
                "brand" => [
                    "title_fa" => "dummy brand",
                ],
                "category" => [
                    "title_fa" => "dummy category",
                ],
                "images" => [
                    "main" => [
                        "storage_ids" => [],
                        "url" => [
                            "feature-src"
                        ],
                        "thumbnail_url" => null,
                        "temporary_id" => null
                    ],
                    "list" => [
                        [
                            "storage_ids" => [],
                            "url" => [
                                "gallery-src-1"
                            ],
                            "thumbnail_url" => null,
                            "temporary_id" => null
                        ],
                        [
                            "storage_ids" => [],
                            "url" => [
                                "gallery-src-2?x-oss-process=image/watermark,image_ZGst"
                            ],
                            "thumbnail_url" => null,
                            "temporary_id" => null
                        ],
                    ],
                ],
                'specifications' => [
                    [
                        'title' => 'مشخصات کلی',
                        'attributes' => [
                            [
                                'title' => 'name1',
                                'values' => ['value1']
                            ],
                            [
                                'title' => 'name2',
                                'values' => ['value2_1', 'value2_2', 'value2_3']
                            ],
                            [
                                'title' => 'name3',
                                'values' => ['value3']
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
}
