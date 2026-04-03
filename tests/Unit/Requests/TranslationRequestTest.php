<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\TranslationRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TranslationRequestTest extends TestCase
{
    public function test_store_rules_require_key_and_content()
    {
        $request = new TranslationRequest();
        $request->setMethod('POST');
        $rules = $request->rules();

        $validator = Validator::make([], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('key', $validator->errors()->toArray());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());
    }

    public function test_authorize_returns_true()
    {
        $request = new TranslationRequest();
        $this->assertTrue($request->authorize());
    }
}
