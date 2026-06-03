<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.edit');
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'jabatan'  => ['required', 'string', 'max:100'],
            'phone'    => ['required', 'string', 'max:20'],
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'role'     => ['required', 'string', 'exists:roles,name'],
            'status'   => ['required', 'in:aktif,block'],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Nama wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'phone.required'   => 'No HP wajib diisi.',
            'email.required'   => 'Email wajib diisi.',
            'email.unique'     => 'Email sudah terdaftar.',
            'role.required'    => 'Role wajib dipilih.',
            'password.min'     => 'Password minimal 8 karakter.',
        ];
    }
}
