<?php

namespace App\Tests\Controller\Customer;

use App\Tests\Controller\BaseControllerTestCase;

class ContactUsControllerTest extends BaseControllerTestCase
{
    public function testSendMail()
    {
        $client = $this->sendRequest(
            'POST',
            $this->route('customer.contact.us.send.mail'),
            [
                'name' => 'test',
                'subject' => 'test',
                'email' => 'test@test.com',
                'phone' => '09121234567',
                'content' => 'This is a test!',
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testSendMailValidationError()
    {
        $client = $this->sendRequest(
            'POST',
            $this->route('customer.contact.us.send.mail'),
            [
                'name' => '',
                'subject' => 'test',
                'email' => 'test@test.com',
                'phone' => '09121234567',
                'content' => 'This is a test!',
            ]
        );

        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }
}
