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
$container[IndexAction::class] = function ($c) {
    return new IndexAction($c->get('view'), $c->get('logger'), $c->get('flash'), $c->get('translator'));
};
$container[SignUpAction::class] = function ($c) {
    return new SignUpAction($c->get('view'), $c->get('logger'), $c->get('flash'), $c->get('translator'));
};
$container[VerificationAction::class] = function ($c) {
    return new VerificationAction($c->get('view'), $c->get('logger'), $c->get('flash'), $c->get('translator'));
};

// Routes
// error
$app->get('/401', NotAuthorizedAction::class)->setName('401');
$app->get('/403', ForbiddenAction::class)->setName('403');
$app->get('/404', NotFoundAction::class)->setName('404');
$app->get('/500', InternalApplicationError::class)->setName('500');

// pages
$app->get('/', IndexAction::class)->setName('/');
$app->map(['GET', 'POST'], '/signup', SignUpAction::class)->setName('signup');
$app->get('/verification/{verificationCode}', VerificationAction::class)->setName('verification');