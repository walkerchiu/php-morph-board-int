<?php

namespace WalkerChiu\MorphBoard\Models\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use WalkerChiu\Core\Models\Forms\FormRequest;

class BoardFormRequest extends FormRequest
{
    /**
     * @Override Illuminate\Foundation\Http\FormRequest::getValidatorInstance
     */
    protected function getValidatorInstance()
    {
        $request = Request::instance();
        $data = $this->all();
        if (
            $request->isMethod('put')
            && empty($data['id'])
            && isset($request->id)
        ) {
            $data['id'] = (int) $request->id;
            $this->getInputSource()->replace($data);
        }

        return parent::getValidatorInstance();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return Array
     */
    public function attributes()
    {
        return [
            'host_type'      => trans('php-morph-category::system.host_type'),
            'host_id'        => trans('php-morph-category::system.host_id'),
            'type'           => trans('php-morph-board::system.type'),
            'user_id'        => trans('php-morph-board::system.user_id'),
            'serial'         => trans('php-morph-board::system.serial'),
            'identifier'     => trans('php-morph-board::system.identifier'),
            'is_highlighted' => trans('php-morph-board::system.is_highlighted'),
            'is_enabled'     => trans('php-morph-board::system.is_enabled'),

            'name'           => trans('php-morph-board::system.name'),
            'description'    => trans('php-morph-board::system.description'),
            'keywords'       => trans('php-morph-board::system.keywords'),
            'remarks'        => trans('php-morph-board::system.remarks'),
            'content'        => trans('php-morph-board::system.content')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return Array
     */
    public function rules()
    {
        $rules = [
            'host_type'      => 'required_with:host_id|string',
            'host_id'        => 'required_with:host_type|integer|min:1',
            'type'           => ['required', Rule::in(config('wk-core.class.morph-board.boardType')::getCodes())],
            'user_id'        => ['required','integer','min:1','exists:'.config('wk-core.table.user').',id'],
            'serial'         => '',
            'identifier'     => '',
            'is_highlighted' => 'required|boolean',
            'is_enabled'     => 'required|boolean',

            'name'           => 'required|string|max:255',
            'description'    => '',
            'keywords'       => '',
            'remarks'        => '',
            'content'        => 'required'
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','integer','min:1','exists:'.config('wk-core.table.morph-board.boards').',id']]);
        } elseif ($request->isMethod('post')) {
            $rules = array_merge($rules, ['id' => ['nullable','integer','min:1','exists:'.config('wk-core.table.morph-board.boards').',id']]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return Array
     */
    public function messages()
    {
        return [
            'id.required'             => trans('php-core::validation.required'),
            'id.integer'              => trans('php-core::validation.integer'),
            'id.min'                  => trans('php-core::validation.min'),
            'id.exists'               => trans('php-core::validation.exists'),
            'host_type.required_with' => trans('php-core::validation.required_with'),
            'host_type.string'        => trans('php-core::validation.string'),
            'host_id.required_with'   => trans('php-core::validation.required_with'),
            'host_id.integer'         => trans('php-core::validation.integer'),
            'host_id.min'             => trans('php-core::validation.min'),
            'type.required'           => trans('php-core::validation.required'),
            'type.in'                 => trans('php-core::validation.in'),
            'user_id.required'        => trans('php-core::validation.required'),
            'user_id.integer'         => trans('php-core::validation.integer'),
            'user_id.min'             => trans('php-core::validation.min'),
            'user_id.exists'          => trans('php-core::validation.exists'),
            'identifier.required'     => trans('php-core::validation.required'),
            'identifier.max'          => trans('php-core::validation.max'),
            'is_highlighted.required' => trans('php-core::validation.required'),
            'is_highlighted.boolean'  => trans('php-core::validation.boolean'),
            'is_enabled.required'     => trans('php-core::validation.required'),
            'is_enabled.boolean'      => trans('php-core::validation.boolean'),

            'name.required'           => trans('php-core::validation.required'),
            'name.string'             => trans('php-core::validation.string'),
            'name.max'                => trans('php-core::validation.max'),
            'content.required'        => trans('php-core::validation.required')
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after( function ($validator) {
            $data = $validator->getData();
            if (
                isset($data['host_type'])
                && isset($data['host_id'])
            ) {
                if (
                    config('wk-morph-board.onoff.site-mall')
                    && !empty(config('wk-core.class.site-mall.site'))
                    && $data['host_type'] == config('wk-core.class.site-mall.site')
                ) {
                    $result = DB::table(config('wk-core.table.site-mall.sites'))
                                ->where('id', $data['host_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));
                } elseif (
                    config('wk-morph-board.onoff.group')
                    && !empty(config('wk-core.class.group.group'))
                    && $data['host_type'] == config('wk-core.class.group.group')
                ) {
                    $result = DB::table(config('wk-core.table.group.groups'))
                                ->where('id', $data['host_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));
                }
            }
            if (isset($data['identifier'])) {
                $result = config('wk-core.class.morph-board.board')::where('identifier', $data['identifier'])
                                ->when(isset($data['host_type']), function ($query) use ($data) {
                                    return $query->where('host_type', $data['host_type']);
                                  })
                                ->when(isset($data['host_id']), function ($query) use ($data) {
                                    return $query->where('host_id', $data['host_id']);
                                  })
                                ->when(isset($data['id']), function ($query) use ($data) {
                                    return $query->where('id', '<>', $data['id']);
                                  })
                                ->exists();
                if ($result)
                    $validator->errors()->add('identifier', trans('php-core::validation.unique', ['attribute' => trans('php-morph-board::system.identifier')]));
            }
        });
    }
}
