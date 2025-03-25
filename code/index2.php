<?php
$categories = [
    [
        'id' => 1,
        'name' => 'Category1',
        'parent' => null,
        'childs' => [
            [
                'id' => 3,
                'name' => 'Category3',
                'parent' => 1,
                'childs' => [
                    [
                        'id' => 5,
                        'name' => 'Category5',
                        'parent' => 3,
                        'childs' => []
                    ],
                ]
            ],
            [
                'id' => 4,
                'name' => 'Category4',
                'parent' => 1,
                'childs' => [
                    [
                        'id' => 6,
                        'name' => 'Category6',
                        'parent' => 4,
                        'childs' => [
                            [
                                'id' => 7,
                                'name' => 'Category7',
                                'parent' => 6,
                                'childs' => []
                            ],
                        ]
                    ],
                ]
            ],
        ]
    ],
    [
        'id' => 2,
        'name' => 'Category2',
        'parent' => null,
        'childs' => [
            [
                'id' => 8,
                'name' => 'Category8',
                'parent' => 2,
                'childs' => [
                    [
                        'id' => 9,
                        'name' => 'Category9',
                        'parent' => 8,
                        'childs' => []
                    ],
                ]
            ],
            [
                'id' => 10,
                'name' => 'Category10',
                'parent' => 2,
                'childs' => []
            ],
        ]
    ]
];


$res = [];
function upLevel($categories, &$res) {
    for($i = 0; $i < count($categories); $i++) {

    foreach ($categories as $key => $category) {
        if (isset($category['id'])) {
            $res[$i][] = $category;
//            unset($category);
            if (!empty($category['childs'])) {

                upLevel($category['childs'], $res);


            }

        }
    }
    }
}
upLevel($categories, $res);

$let = '';