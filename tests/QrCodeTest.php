<?php

use Farzai\PromptPay\QrCode;
use Psr\Http\Message\ResponseInterface;

it('can render qr code as psr response', function () {
    $qrCode = new QrCode('00020101021129370016A000000677010111011300668999999995802TH53037646304FE29');

    $response = $qrCode->toPsrResponse();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($response->getStatusCode())->toBe(200);
    expect($response->getHeaderLine('Content-Type'))->toBe('image/png');
    expect($response->getBody()->getContents())->toBe($qrCode->asPng());
});

it('can render qr code as data uri', function () {
    $qrCode = new QrCode('00020101021129370016A000000677010111011300668999999995802TH53037646304FE29');

    $dataUri = $qrCode->asDataUri();

    expect($dataUri)->toBe('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAIAAAD/gAIDAAAACXBIWXMAAA7EAAAOxAGVKw4bAAACfUlEQVR4nO2cQW7kMAwE42D//+XJzQlWDsGiSA12UXUcO5LTYIMyKfl6vV4fkuPz3Q/wL6FYAMUCKBZAsQB/1p+u69oZMUiv68jrzeie+1LwS43H/8LIAigW4MGGN2i9mgn7TY+sXltHDiZFUzxiZAEUCxDZ8CYIzkzuC1yDRs7kPpSLg7keMbIAigVI2bBGxiOZe4JlasZ9jRhZAMUCDNpwJfMG15UfJzCyAIoFSNlwM7ZrS9DAoTX37TvUyAIoFiCy4WaxsZahUBW0tqYtY2QBFAtwza3iNq1R61OMYmQBFAvA+oajXbnfmKuCUhcbWQDFAqT6hne4Iod2rSpXUO20cXYjC6BYgIdF6WYhJfirWl6rLTgn/GhkARQLMPhu+D3HfA5FIwdPaDZsQ7EAUTasddLXS8HItdZ8lx/pG6WRBVAsQJQNG/PIXzffzPmoNo7ZsA3FAhR30dQcUTtPsV7qqtP6bjiIYgFS74abm6I3zzfV6qKZucyGgygWYHdP6ZxDM4ZC+dFK6VEUC8AqpXP7WNqroLU9PGbDNhQLEO2iyZyDKFcdd8bZPG9oNjyBYgHeUynt6mUElybOLRpZAMUCpPqGXXXRh+m7s1gGs+EJFAvwnhMWmZtrO8xr2+TMhv0oFuDEl9m6WvzBgJm312BAs2E/igU48WW2zR7E5t6b4FEzDv2JkQVQLMDgl9mCP0f5CP1SO/ER3PMTIwugWICjX2a7mesvIGfRCo+RBVAswKANa929IMFtZsz1wYJJHzGyAIoFOPFltpojNjspNafHGFkAxQIMfpntJuNi1Ghoz49mw34UC3Di9P1/g5EFUCyAYgEUC6BYAMUCfAEmaZrFGCw8qwAAAABJRU5ErkJggg==');
});

it('can render qr code as base64', function () {
    $qrCode = new QrCode('00020101021129370016A000000677010111011300668999999995802TH53037646304FE29');

    $base64 = $qrCode->asBase64();

    expect($base64)->toBe('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAIAAAD/gAIDAAAACXBIWXMAAA7EAAAOxAGVKw4bAAACfUlEQVR4nO2cQW7kMAwE42D//+XJzQlWDsGiSA12UXUcO5LTYIMyKfl6vV4fkuPz3Q/wL6FYAMUCKBZAsQB/1p+u69oZMUiv68jrzeie+1LwS43H/8LIAigW4MGGN2i9mgn7TY+sXltHDiZFUzxiZAEUCxDZ8CYIzkzuC1yDRs7kPpSLg7keMbIAigVI2bBGxiOZe4JlasZ9jRhZAMUCDNpwJfMG15UfJzCyAIoFSNlwM7ZrS9DAoTX37TvUyAIoFiCy4WaxsZahUBW0tqYtY2QBFAtwza3iNq1R61OMYmQBFAvA+oajXbnfmKuCUhcbWQDFAqT6hne4Iod2rSpXUO20cXYjC6BYgIdF6WYhJfirWl6rLTgn/GhkARQLMPhu+D3HfA5FIwdPaDZsQ7EAUTasddLXS8HItdZ8lx/pG6WRBVAsQJQNG/PIXzffzPmoNo7ZsA3FAhR30dQcUTtPsV7qqtP6bjiIYgFS74abm6I3zzfV6qKZucyGgygWYHdP6ZxDM4ZC+dFK6VEUC8AqpXP7WNqroLU9PGbDNhQLEO2iyZyDKFcdd8bZPG9oNjyBYgHeUynt6mUElybOLRpZAMUCpPqGXXXRh+m7s1gGs+EJFAvwnhMWmZtrO8xr2+TMhv0oFuDEl9m6WvzBgJm312BAs2E/igU48WW2zR7E5t6b4FEzDv2JkQVQLMDgl9mCP0f5CP1SO/ER3PMTIwugWICjX2a7mesvIGfRCo+RBVAswKANa929IMFtZsz1wYJJHzGyAIoFOPFltpojNjspNafHGFkAxQIMfpntJuNi1Ghoz49mw34UC3Di9P1/g5EFUCyAYgEUC6BYAMUCfAEmaZrFGCw8qwAAAABJRU5ErkJggg==');
});

it('can generate qr code and save to file', function () {
    $qrCode = new QrCode('00020101021129370016A000000677010111011300668999999995802TH53037646304FE29');

    $qrCode->save('qrcode.png');

    expect(file_exists('qrcode.png'))->toBeTrue();

    unlink('qrcode.png');
});
