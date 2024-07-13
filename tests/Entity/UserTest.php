<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @test
     */
    public function addNewUserTest(): void
    {
        $user = new User();
        $user->setUserName('Claire');

        $this->assertEquals('Claire', $user->getUserName());
    }
}
