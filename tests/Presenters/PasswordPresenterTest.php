<?php

require_once(ROOT_DIR . 'Pages/PasswordPage.php');
require_once(ROOT_DIR . 'Presenters/PasswordPresenter.php');

class PasswordPresenterTest extends TestBase
{
    public function testResetsPassword()
    {
        $page = $this->createMock('IPasswordPage');
        $userRepo = $this->createMock('IUserRepository');
        $encryption = $this->createMock('PasswordEncryption');
        $user =  $this->createMock('User');

        $newPassword = 'new password';
        $encryptedValue = 'enc';
        $salt = 'salt';

        $encryptedPassword = new EncryptedPassword($encryptedValue, $salt);

        $presenter = new PasswordPresenter($page, $userRepo, $encryption);

        $page->expects($this->once())
                ->method('ResettingPassword')
                ->will($this->returnValue(true));

        $page->expects($this->once())
                ->method('IsValid')
                ->will($this->returnValue(true));

        $page->expects($this->atLeastOnce())
                ->method('GetPassword')
                ->will($this->returnValue($newPassword));

        $userRepo->expects($this->atLeastOnce())
                ->method('LoadById')
                ->with($this->equalTo($this->fakeUser->UserId))
                ->will($this->returnValue($user));

        $encryption->expects($this->once())
                ->method('EncryptPassword')
                ->with($this->equalTo($newPassword))
                ->will($this->returnValue($encryptedPassword));

        $user->expects($this->once())
                ->method('ChangePassword')
                ->with($this->equalTo($encryptedValue), $this->equalTo($salt));

        $userRepo->expects($this->once())
                ->method('Update')
                ->with($this->equalTo($user));

        $page->expects($this->once())
                ->method('ShowResetPasswordSuccess')
                ->will($this->returnValue(true));

        $presenter->PageLoad();
    }

    public function testPasswordValidatorComparesStoredPasswordAgainstProvidedPassword()
    {
        $passwordEncryption = new PasswordEncryption();
        $salt = $passwordEncryption->Salt();

        $current = "some password";
        $user = new User();

        $encrypted = $passwordEncryption->Encrypt($current, $salt);

        $user->encryptedPassword = $encrypted;
        $user->passwordSalt = $salt;

        $validator = new PasswordValidator($current, $user);

        $validator->Validate();

        $this->assertTrue($validator->IsValid());
    }
}
