<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NoteControllerTest extends TestCase
{
    /**
     * @test 
     * for successfull notecreation
     */
    public function test_SuccessfullCreateNote()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MjQ4NjUxMywiZXhwIjoxNjQyNDkwMTEzLCJuYmYiOjE2NDI0ODY1MTMsImp0aSI6InloR2w3MGdIUGk1dldTR0kiLCJzdWIiOjksInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.ez6jbebrIeC3suoh96VZhwwh9x8Wb1ktEKjj0IPKab0'
            ])->json('POST', '/api/auth/createnote',
            [
                "title" => "fyyyy",
                "description" => "phppp",
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'notes created successfully']);
    }
    /**
     * @test
     * for FailNoteCreation
     * Invalid authorization token
     */
    public function test_FailNoteCreation()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MjQ4NjUxMywiZXhwIjoxNjQyNDkwMTEzLCJuYmYiOjE2NDI0ODY1MTMsImp0aSI6InloR2w3MGdIUGk1dldTR0kiLCJzdWIiOjksInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.ez6jbebrIeC3suoh96VZhwwh9x8Wb1ktEKjj0IPKab0'
            ])->json('POST', '/api/auth/createnote',
            [
                "title" => "fyyyyzzz",
                "description" => "phppyyy",
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Invalid authorization token']);
    }

    /**
     * @test 
     * for Successfull Note update
     */

    public function test_SuccessfullUpdateNote()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MjU2NzMwMSwiZXhwIjoxNjQyNTcwOTAxLCJuYmYiOjE2NDI1NjczMDEsImp0aSI6IjZFZTFpS1FqZHd1NjIzR08iLCJzdWIiOjksInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.3tXavu4g9QVlS9byH215sMC3VjQZIbvpnjc2EgJvw9o'
            ])->json('POST', '/api/auth/updatenote',
            [
                "id" => "8",
                "title" => "fyyyyyiik",
                "description" => "phppplllkkhh",
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note updated Sucessfully']);
    }

    /**
     * @test 
     * for Unsuccessfull Note Updatation
     */

    public function test_FailUpdateNote()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MjU2NzMwMSwiZXhwIjoxNjQyNTcwOTAxLCJuYmYiOjE2NDI1NjczMDEsImp0aSI6IjZFZTFpS1FqZHd1NjIzR08iLCJzdWIiOjksInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.3tXavu4g9QVlS9byH215sMC3VjQZIbvpnjc2EgJvw9o'
            ])->json('POST', '/api/auth/updatenote',
            [
                "id" => "12",
                "title" => "fundonote 2 Test",
                "description" => "descrip 2 Test",
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Notes not Found']);
    }

    /**
     * @test 
     * for Successfull Deletion of Node
     */
    public function test_SuccessfullDeleteNote()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MjU2NzMwMSwiZXhwIjoxNjQyNTcwOTAxLCJuYmYiOjE2NDI1NjczMDEsImp0aSI6IjZFZTFpS1FqZHd1NjIzR08iLCJzdWIiOjksInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.3tXavu4g9QVlS9byH215sMC3VjQZIbvpnjc2EgJvw9o'
            ])->json('POST', '/api/auth/deletenote',
            [
                "id" => "4"
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Note deleted Sucessfully']);
    }

    /**
     * @test 
     * for Unsuccessfull Deletion of note
     */
    public function test_FailNoteDeletion()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MjU2NzMwMSwiZXhwIjoxNjQyNTcwOTAxLCJuYmYiOjE2NDI1NjczMDEsImp0aSI6IjZFZTFpS1FqZHd1NjIzR08iLCJzdWIiOjksInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.3tXavu4g9QVlS9byH215sMC3VjQZIbvpnjc2EgJvw9o'
            ])->json('POST', '/api/auth/deletenote',
            [
                "id" => "17"
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Notes not Found']);
    }

}
