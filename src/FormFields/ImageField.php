<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Shemi\Laradmin\Contracts\FieldHasBrowseValue;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class ImageField extends FormFormField implements FieldHasBrowseValue
{

    protected $codename = "image";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.image', compact(
            'field',
            'type',
            'model',
            'data'
        ));
    }

    public function transformRequest(Field $field, $data)
    {
        if($data instanceof Collection) {
            return $data;
        }

        if(! $data) {
            return collect([]);
        }

        $id = array_get($data, 'customAttributes.id', 0);

        $data = (object) [
            'is_new' => ! ((bool) $id),
            'id' => $id,
            'order' => 0,
            'temp_path' => array_get($data, 'customAttributes.temp_path', ""),
            'name' => array_get($data, 'name', ""),
            'hash_name' => array_get($data, 'customAttributes.md5_name', ""),
            'caption' => array_get($data, 'customAttributes.caption', ""),
            'alt' => array_get($data, 'customAttributes.alt', ""),
        ];

        return collect([$data]);
    }

    public function getValidationRoles(Field $field)
    {
        return false;
    }

    public function renderBrowseValue(Field $field, Model $model)
    {
        $media = $model->getMedia($field->key)->first();

        if(! $media) {
            return "";
        }

        $src = route('laradmin.serve', [
            'mediaId' => $media->id,
            'fileName' => $media->name,
            'pc' => $field->getTemplateOption('preview_conversion', null)
        ]);


        return "<img src='{$src}'>";
    }

    public function structure()
    {
        $structure = parent::structure();

        return array_replace_recursive($structure, [
            'media' => [
                'disk' => config(
                        'medialibrary.defaultFilesystem',
                        config('filesystems.default')
                    )
            ],
            'template_options' => [
                'preview_conversion' => null
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->media();
        $schema->template_options->properties(function(Blueprint $schema) {
            $schema->string('preview_conversion')
                ->nullable()
                ->required();
        });
    }

}