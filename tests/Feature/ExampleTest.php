<?php

test('guests are redirected to login from the home route', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
