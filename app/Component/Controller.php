<?php


namespace App\Component;


use Altech\Model\Entity\User;
use Altech\Model\Repository\UserRepository;
use Altech\Service\Mailer;
use App\KernelFoundation\Database;
use App\KernelFoundation\Request;
use App\KernelFoundation\Response;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;

abstract class Controller
{
    const DEFAULT_LAYOUT = "/layout.php";

    private $request;
    private $user;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $repo = $this->getRepository(UserRepository::class);
        $id = $this->request->session->get('auth');
        if ($id) {
            $this->user = $repo->findbyId($id);
            if (!$this->user)
                throw new Exception("Unknown user (id: $id)");
        }
    }

    protected function getRepository(string $class): Repository
    {
        try {
            return Database::getRepository($class);
        } catch (Exception $e) {
            die("unable to reach Repository $class");
        }
    }


    protected function redirect(string $uri){
        header("Location: $uri");
        exit();
    }

    protected function render(string $view, array $param = [], Response $response = null, $layout = self::DEFAULT_LAYOUT): Response
    {
        // Check that file does exist otherwise throw exception
        if (!file_exists(View::PATH_TO_VIEWS . $view)) {
            throw new Exception('no template file ' . $view . ' present in directory ' . View::PATH_TO_VIEWS);
        }

        //Generate html page
        // Start output buffer
        ob_start();
        $role = $this->request->session->get('role') ?? "";
        $user = $this->user;
        extract($param);
        include View::PATH_TO_VIEWS . $view;
        if($layout){
            $body = ob_get_contents();
            ob_end_clean();

            ob_start();
            include View::PATH_TO_VIEWS . $layout;
            $content = ob_get_contents();
            ob_end_clean();
        }else {
            $content = ob_get_contents();
            ob_end_clean();
        }


        $response = $response ?? new Response();
        $response->setContent($content);

        return $response;
    }

    protected function getRequest(): Request
    {
        return $this->request;
    }

    protected function getMailer(): Mailer
    {
        return Mailer::getInstance();
    }

    protected function getUser(): ?User
    {
       return $this->user;
    }
}