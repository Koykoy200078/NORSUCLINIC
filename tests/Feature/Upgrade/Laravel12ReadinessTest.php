<?php

namespace Tests\Feature\Upgrade;

use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Tests\TestCase;

/**
 * Upgrade plan U2 (Laravel 12): what Laravel 12 changed under the application and has to keep working.
 *
 * - Laravel 12 builds the uploaded-file objects of a request again every time they are asked for, and a browser upload
 *   is Symfony's own UploadedFile, which cannot be built once its temp file is gone (the media library moves it). Code
 *   that reads the whole request late (the audit trail after the answer, the validation redirect) must not rebuild them
 *   (the audit trail has its own test in AuditTrailTest);
 * - the skeleton's `local` disk now points at storage/app/private; this application's own config keeps storage/app,
 *   where the backups, the QA files and the consultation images already are.
 */
class Laravel12ReadinessTest extends TestCase
{
    public function test_a_validation_redirect_survives_an_upload_whose_temp_file_is_already_gone(): void
    {
        $picture = tempnam(sys_get_temp_dir(), 'val');
        $fake = UploadedFile::fake()->image('photo.png', 40, 40); // kept in a variable: the temp file lives as long as this object
        copy($fake->getPathname(), $picture);
        $request = Request::create('/admin/profile/update', 'PUT', ['first_name' => ''], [], [
            'image' => new SymfonyUploadedFile($picture, 'photo.png', 'image/png', null, true),
        ]);
        unlink($picture); // the media library moved the upload away before a later step refused the form

        $failure = new ValidationException(Validator::make(['first_name' => ''], ['first_name' => 'required']));

        $response = app(ExceptionHandler::class)->render($request, $failure);

        $this->assertInstanceOf(Handler::class, app(ExceptionHandler::class));
        $this->assertInstanceOf(RedirectResponse::class, $response, 'the person must get the form back with the message, not an error page');
    }

    public function test_the_local_disk_keeps_the_storage_app_root(): void
    {
        $this->assertSame(storage_path('app'), config('filesystems.disks.local.root'));
    }
}
