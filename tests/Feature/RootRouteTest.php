<?php

test('guest visiting the root is redirected through the dashboard to login', function () {
    $this->get('/')->assertRedirect('/dashboard');

    $this->get('/dashboard')->assertRedirect(route('login'));
});

test('the login page renders for guests', function () {
    $this->get(route('login'))->assertOk();
});
