<?php

use Illuminate\Support\Str;

class AuthenticationCest
{
    protected $endpoint = "/auth";
    protected $testUser = "tester@email.com";

    public function _before(ApiTester $I)
    {
        // create user data using factory
        factory(\App\User::class)->create([
            'email' => $this->testUser
        ]);
    }

    // tests
    public function loginUserViaAPI(\ApiTester $I)
    {
        $url = $this->endpoint . "/login";
        $I->wantTo("Test Success Case - Logging in as user via email and password");
        $I->expectTo("See a success response and user data returned");

        $I->setHeadersForApiCall();
        $I->sendPOST($url, [
            'email' => 'tester@email.com',
            'password' => 'password'
        ]);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(array('status' => 'Success', 'code' => \App\Helpers\ResponseCodes::ACTION_SUCCESSFUL));
    }

    public function dontLoginUnauthorizedAdmin(ApiTester $I)
    {
        $url = $this->endpoint . "/login";

        $I->wantTo("Test Fail Case - Don't log in User with invalid credentials");
        $I->expectTo("See a error response");

        $I->setHeadersForApiCall();
        $I->sendPOST($url, [
            'email' => 'jamesbond@example.net',
            'password' => 'password'
        ]);

        $I->seeResponseContainsJson([
            "status" => "error"
        ]);
    }
}
