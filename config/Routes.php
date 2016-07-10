<?php

// Action factory
// error
$container[NotFoundAction::class] = function ($c) {
    return new NotFoundAction($c->get('view'), $c->get('logger'), $c->get('flash'), $c->get('translator'));
};
$container[NotAuthorizedAction::class] = function ($c) {
    return new NotAuthorizedAction($c->get('view'), $c->get('logger'), $c->get('flash'), $c->get('translator'));
};
$container[ForbiddenAction::class] = function ($c) {
    return new ForbiddenAction($c->get('view'), $c->get('logger'), $c->get('flash'), $c->get('translator'));
};
$container[InternalApplicationError::class] = function ($c) {
    return new InternalApplicationError($c->get('view'), $c->get('logger'), $c->get('flash'), $c->get('translator'));
};

// pages
$container[HomeAction::class] = function ($c) {
    return new HomeAction($c->get('view'), $c->get('logger'), $c->get('flash'), $c->get('translator'));
};
$container[SignUpAction::class] = function ($c) {
    return new SignUpAction($c->get('view'), $c->get('logger'), $c->get('flash'), $c->get('translator'), $c->get('router'));
};
$container[VerificationAction::class] = function ($c) {
    return new VerificationAction($c->get('view'), $c->get('logger'), $c->get('flash'), $c->get('translator'));
};
$container[DeleteAction::class] = function ($c) {
    return new DeleteAction($c->get('view'), $c->get('logger'), $c->get('flash'), $c->get('translator'));
};

// Routes
// error
$app->get('/401', NotAuthorizedAction::class)->setName('401');
$app->get('/403', ForbiddenAction::class)->setName('403');
$app->get('/404', NotFoundAction::class)->setName('404');
$app->get('/500', InternalApplicationError::class)->setName('500');

// pages
$app->get('/', HomeAction::class)->setName('/');
$app->map(['GET', 'POST'], '/signup', SignUpAction::class)->setName('signup');
$app->get('/verification/{verificationCode}', VerificationAction::class)->setName('verification');
$app->map(['GET', 'POST'], '/delete', DeleteAction::class)->setName('delete');