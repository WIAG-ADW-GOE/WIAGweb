<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator {

    use TargetPathTrait;

    private $userRepository;
    private $router;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(UserRepository $userRepository, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder) {
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'wiag_login' && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        // send this as `credentials` to `getUser`
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(Security::LAST_USERNAME, $credentials['email']);

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
        return $this->userRepository->findOneBy(['email' => $credentials['email']]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        //dd($user);
        // dd($this->passwordEncoder->isPasswordValid($user, $credentials['password']));
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    // public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    // {
    //     // dd('Access denied');
    // }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        /* If nothing is done here, the request is handled by the controller.
         * This is should be done for an API request.
         */

        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);

        if ($targetPath) return new RedirectResponse($targetPath);
        else return new RedirectResponse($this->router->generate('wiag_welcome'));
    }

    // The parent class does the right thing, e.g. for anoymous users
    // public function start(Request $request, AuthenticationException $authException = null)
    // {
    //     // todo
    // }

    public function supportsRememberMe()
    {
        // todo
    }

    public function getLoginUrl()
    {
        return $this->router->generate('wiag_login');
    }
}
