<?php declare(strict_types=1);

#
# MIT License
#
# Copyright (c) 2024 Colin Atkins
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.
#

namespace Atkins\Pagedoctor\Initializers;

use Atkins\Pagedoctor\PagedoctorException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

final class TCA
{

    const MAXIMUM_TYPE_COLUMNS = 50;

    public static function initialize(): void
    {
        self::modifySelectItems();
        self::setLabelRenderer();
        self::addCtrlColumns('text');
        self::addCtrlColumns('integer');
        self::addCtrlColumns('asset');
        self::addCtrlColumns('repeatable');
        self::addCtrlColumns('parenttable');
    }

    # Sets listeners to modify select items for specific columns.
    private static function modifySelectItems(): void
    {
        array_map(function ($column) {
            $GLOBALS['TCA']['tt_content']['columns'][$column]['config']['itemsProcFunc'] = match ($column) {
                'colPos' => \Atkins\Pagedoctor\Backend\Form\ColPosModifier::class . '->process',
                'CType' => \Atkins\Pagedoctor\Backend\Form\CTypeModifier::class . '->process',
            };
        }, ['colPos', 'CType']);
    }

    # Load the label renderer which renders a label for list views.
    private static function setLabelRenderer(): void
    {
        $GLOBALS['TCA']['tt_content']['ctrl']['label_userFunc'] = \Atkins\Pagedoctor\Backend\Preview\LabelRenderer::class . '->render';
    }

    public static function addCtrlColumns(string $type): void
    {
        switch ($type) {
            case 'text':
                $columns = self::generateTextColumn();
                break;
            case 'integer':
                $columns = self::generateIntegerColumn();
                break;
            case 'asset':
                $columns = self::generateAssetColumn();
                break;
            case 'repeatable':
                $columns = new \AppendIterator();
                $columns->append(self::generateEmbedColumn());
                $columns->append(self::generateEmbedParentColumn());
                $columns->append(self::generateReferenceColumn());
                break;
            case 'parenttable':
                $columns = self::generateParentTableColumn();
                break;
            default:
                throw new PagedoctorException('Specified column type is not known');
        }

        foreach ($columns as $column) {
            ExtensionManagementUtility::addTCAcolumns('tt_content', $column);
        }
    }

    private static function generateRounds(): \Generator
    {
        for ($i = 1; $i <= self::MAXIMUM_TYPE_COLUMNS; $i++) {
            yield $i;
        }
    }

    private static function generateTextColumn(): \Generator
    {
        foreach (self::generateRounds() as $index) {
            yield [
                "tx_pagedoctor_text_{$index}" => [
                    #'exclude' => 1,
                    'label' => "text_{$index}",
                    'config' => [
                        'type' => 'text',
                    ],
                ]
            ];
        }
    }

    private static function generateIntegerColumn(): \Generator
    {
        foreach (self::generateRounds() as $index) {
            yield [
                "tx_pagedoctor_integer_{$index}" => [
                    #'exclude' => 1,
                    'label' => "integer_{$index}",
                    'config' => [
                        'type' => 'number',
                    ],
                ]
            ];
        }
    }

    private static function generateAssetColumn(): \Generator
    {
        foreach (self::generateRounds() as $index) {
            yield [
                "tx_pagedoctor_asset_{$index}" => [
                    #'exclude' => 1,
                    'label' => "asset_{$index}",
                    'config' => [
                        'type' => 'file',
                    ],
                ]
            ];
        }
    }

    private static function generateEmbedColumn(): \Generator
    {
        foreach (self::generateRounds() as $index) {
            yield [
                "tx_pagedoctor_embed_{$index}" => [
                    #'exclude' => 1,
                    'label' => "embed_{$index}",
                    'config' => [
                        'type' => 'inline',
                        'foreign_table' => 'tt_content',
                        'foreign_table_field'=>'tx_pagedoctor_tablename',
                        'foreign_field' => "tx_pagedoctor_embed_parent_{$index}",
                        'foreign_match_fields'=>[
                            'tx_pagedoctor_fieldname'=>"tx_pagedoctor_embed_{$index}"
                        ],
                        'foreign_sortby' => 'sorting',
                    ],
                ]
            ];
        }
    }

    private static function generateEmbedParentColumn(): \Generator
    {
        foreach (self::generateRounds() as $index) {
            yield [
                "tx_pagedoctor_embed_parent_{$index}" => [
                    'config' => [
                        'type' => 'select',
                        'renderType' => 'selectSingle',
                        'default' => 0,
                        'foreign_table' => 'tt_content',
                        'foreign_table_where' => 'AND tt_content.pid=###CURRENT_PID### AND tt_content.sys_language_uid IN (-1, ###REC_FIELD_sys_language_uid###)',

                    ],
                ]
            ];
        }
    }

    private static function generateReferenceColumn(): \Generator
    {
        foreach (self::generateRounds() as $index) {
            yield [
                "tx_pagedoctor_reference_{$index}" => [
                    #'exclude' => 1,
                    'label' => "reference_{$index}",
                    'config' => [
                        'type' => 'group',
                        'allowed' => 'tt_content,pages,be_groups,be_users,fe_users,fe_groups,backend_layout',
                        'maxitems' => 99999
                    ],
                ]
            ];
        }
    }

    private static function generateParentTableColumn(): \Generator
    {
        yield [
            'tx_pagedoctor_parenttable' => [
                'config' => [
                    'type' => 'passthrough'
                ]
            ]
        ];
    }
}