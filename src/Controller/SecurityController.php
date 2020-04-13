<?php

namespace Altech\Controller;

use Altech\Model\Repository\UserRepository;
use App\Component\Controller;
use App\KernelFoundation\Request;

class SecurityController extends Controller
{
    const SECRET_SALT = ",^GH'7hq}LJgL`CU";

    public function login()
    {
        $req = $this->getRequest();

        // If request is post then form has been submitted
        if ($req->is(Request::METHOD_POST)) {
            $form = $req->parameters['form'];

            if (!empty($form->get('email')) && !empty($form->get('password'))) {
                /** @var UserRepository $repository */
                $repository = $this->getRepository(UserRepository::class);

                $user = $repository->findByEmail($form->get('email'));

                if ($user && password_verify($form->get('password'), $user->getPassword())) {

                    $_SESSION['auth'] = $user->getId();
                    $_SESSION['role'] = $user->getId();
                    $this->redirect('/client');
                }

            }
        }

        return $this->render('');
    }

    public function logout()
    {
        unset($_SESSION['auth'], $_SESSION['role']);
        $this->redirect('/');
    }

    public function recoverPassword()
    {
        $req = $this->getRequest();
        $status = false;

        if ($req->is(Request::METHOD_POST)) {
            $status = true;
            $form = $req->parameters['form'];

            if (!empty($form->get('email'))) {
                /** @var UserRepository $repository */
                $repository = $this->getRepository(UserRepository::class);

                $user = $repository->findByEmail($form->get('email'));
                if ($user) {
                    $user->setRecoverToken( sha1(mt_rand(1, 90000) . self::SECRET_SALT));
                    $repository->update($user);

                    //TODO: send email now
                }
            }

        }
        return $this->render('/security/password-recover.php', [
            "status" => $status
        ], null, null);
    }

    public function resetPassword(string $token)
    {
        $req = $this->getRequest();
        /** @var UserRepository $repository */
        $repository = $this->getRepository(UserRepository::class);

        $user = $repository->findByToken($token);
        if ($user) {
            $status = false;
            $error = "";
            if ($req->is(Request::METHOD_POST)) {
                $form = $req->parameters['form'];

                if (!empty($form->get('password')) && !empty($form->get('password_confirm'))) {
                    $user->setPassword(password_hash($form->get('password'),  PASSWORD_BCRYPT));
                    $user->setRecoverToken(null);

                    $repository->update($user);
                }else {
                    $error = "les mots de passes sont différents.";
                }
            }

            return $this->render('/security/password-reset.php', [
                "status" => $status,
                "error" => $error
            ], null, null);
        }
        $this->redirect("/");
    }
}