<?php namespace App\Services\Mail;

use App;
use Blade;
use App\User;
use App\Reply;
use Exception;
use Throwable;
use App\Ticket;
use Carbon\Carbon;
use App\Services\Settings;
use Illuminate\Support\Arr;
use Illuminate\View\Factory;
use Illuminate\Mail\Markdown;
use Faker\Generator as Faker;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class MailTemplatePreview
{
    /**
     * @var Faker
     */
    private $faker;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Factory
     */
    private $view;

    /**
     * MailTemplatePreview constructor.
     *
     * @param Faker $faker
     * @param Settings $settings
     */
    public function __construct(Faker $faker, Settings $settings)
    {
        $this->faker = $faker;
        $this->settings = $settings;
    }

    /**
     * Render specified string contents using
     * blade/php compilers and mock data.
     *
     * @param array $config
     * @return array
     * @throws Exception
     * @throws FatalThrowableError
     */
    public function render($config)
    {
        $contents = Arr::get($config, 'contents');
        $plain    = Arr::get($config, 'plain', false);
        $markdown = Arr::get($config, 'markdown', false);

        $data = $this->getMockData();
        $this->view = $this->makeViewFactory();

        $php = Blade::compileString($contents);

        $obLevel = ob_get_level();
        ob_start();
        extract($data, EXTR_SKIP);

        try {
            eval('?' . '>' . $php);
        } catch (Exception $e) {
            while (ob_get_level() > $obLevel) ob_end_clean();
            throw $e;
        } catch (Throwable $e) {
            while (ob_get_level() > $obLevel) ob_end_clean();
            throw new FatalThrowableError($e);
        }

        $contents = ob_get_clean();

        if ($markdown) {
            $contents = $this->handleMarkdown($contents, $plain);
        }

        return ['contents' => $contents];
    }

    /**
     * Render markdown mail template.
     *
     * @param string $contents
     * @param boolean $plain
     * @return string
     */
    private function handleMarkdown($contents, $plain)
    {
        if ($plain) {
            return preg_replace("/[\r\n]{2,}/", "\n\n", $contents);
        } else {
            return (new CssToInlineStyles)->convert(
                $contents, $this->view->make('mail::themes.default')->render()
            );
        }
    }

    /**
     * Get mock data for rendering mail template previews.
     *
     * @return array
     */
    private function getMockData()
    {
        $ticket = factory(Ticket::class)->make(['latest_replies' => factory(Reply::class, 5)->make()]);

        $ticket->latest_replies->each(function(Reply $reply, $index) {
            $reply->setRelation('user', factory(User::class)->make());
            $reply->created_at = Carbon::now()->addHours(-($index+1));
            $reply->body = $this->faker->realText();
        });

        $data = [
            'reference'     => str_random(),
            'ticket'        => $ticket,
            'reply'         => factory(Reply::class)->make(),
            'recipient'     => $this->faker->email,
            'reason'        => $this->faker->sentence(),
            'description'   => $this->faker->realText(),
            'headers'       => $this->faker->text(),
            'body'          => $this->faker->realText(),
            'siteName'      => $this->settings->get('branding.site_name')
        ];

        $data['__env'] = app(\Illuminate\View\Factory::class);

        return $data;
    }

    /**
     * Make and configure view factory instance.
     *
     * @return Factory
     */
    private function makeViewFactory()
    {
        $factory  = App::make(Factory::class);
        $markdown = App::make(Markdown::class);
        $markdown->loadComponentsFrom([resource_path('views/vendor/mail')]);

        $factory->replaceNamespace(
            'mail', $markdown->htmlComponentPaths()
        );

        return $factory;
    }
}