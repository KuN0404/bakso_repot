<?php

test('the application redirects guests at / to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
