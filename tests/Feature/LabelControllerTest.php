<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LabelControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_IfGiven_Note_idAnd_LabelName_ShouldValidate_AndReturnSuccessStatus()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MzA4NDY0NiwiZXhwIjoxNjQzMDg4MjQ2LCJuYmYiOjE2NDMwODQ2NDYsImp0aSI6IllBZnhoUzlERzJXTGxNSTkiLCJzdWIiOjEsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.qzBsO40W4qlKVGKqmJqbXA-1Lg-df35ojq-AiO6sdRo'
        ])->json('POST', '/api/auth/createlable', 
        [
            "labelname" => "new test new",
            "note_id"   => "1",
        ]);

        $response->assertStatus(201)->assertJson(['message' => 'Label added Sucessfully']);
    }
     public function test_FailLableCreation()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MzA4NDY0NiwiZXhwIjoxNjQzMDg4MjQ2LCJuYmYiOjE2NDMwODQ2NDYsImp0aSI6IllBZnhoUzlERzJXTGxNSTkiLCJzdWIiOjEsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.qzBsO40W4qlKVGKqmJqbXA-1Lg-df35ojq-AiO6sdRo'
            ])->json('POST', '/api/auth/createlable',
            [
                "note_id" => "1",
                "labelname" => "new test new",
            ]);
        $response->assertStatus(401)->assertJson(['message' => 'Label Name already exists']);
    }
    public function test_SuccessfullDeleteLable()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzNDcwMjA0NywiZXhwIjoxNjM0NzA1NjQ3LCJuYmYiOjE2MzQ3MDIwNDcsImp0aSI6Im1lQTk4NnNIUGl6WXltRUoiLCJzdWIiOjgsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.l60uzderg6p9vxDrcU18ZzYsKRDRmxfv2ptHsdkH1RM'
            ])->json('POST', '/api/auth/deletelable',
            [
                "id" =>"14"   //LableId
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Lable deleted']);
    }
    public function test_FailLableDeletion()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzNDcwMjUzMiwiZXhwIjoxNjM0NzA2MTMyLCJuYmYiOjE2MzQ3MDI1MzIsImp0aSI6ImhZNXRWZGpzNHJZUXBYck4iLCJzdWIiOjgsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.vHAuvkRE53Dhp2YBOtehDyNTRwMm6oCIgYWPVlRrPaA '
            ])->json('POST', '/api/auth/deletelable',
            [
                "id" => "17"
            ]);
        $response->assertStatus(400)->assertJson(['message' => 'lable not found']);
    }
    public function test_SuccessfullupdateLable()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzNDY1MzEzNywiZXhwIjoxNjM0NjU2NzM3LCJuYmYiOjE2MzQ2NTMxMzcsImp0aSI6IlB6YXZRRFVheGVXclVRMnMiLCJzdWIiOjgsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.OyNSvp4q1i_gju6cV_OOj-goD3T0qg_mfYhcFt7VeoA '
            ])->json('POST', '/api/auth/updatenote',
            [
                "id" => "6",
                "lable_name" => "Lable Test success",
            ]);
            $response->assertStatus(201)->assertJson(['message' => 'Updation done']);
    }
}
