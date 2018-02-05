<?php namespace App\Http\Controllers;

use App\Services\Settings;
use App\Services\TagRepository;
use App\Tag;
use Auth;
use Illuminate\Http\Request;

class NewTicketCategoriesController extends Controller
{
    /**
     * Laravel request instance.
     *
     * @var Request
     */
    private $request;

    /**
     * TagRepository instance.
     *
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * TagController constructor.
     *
     * @param Request $request
     * @param TagRepository $tagRepository
     * @param Settings $settings
     */
    public function __construct(Request $request, TagRepository $tagRepository, Settings $settings)
    {
        $this->request = $request;
        $this->settings = $settings;
        $this->tagRepository = $tagRepository;
    }

    /**
     * Get new ticket categories.
     *
     * @return array
     */
    public function index()
    {
        $this->authorize('index', Tag::class);

        $tags = $this->tagRepository->paginateTags(['type' => 'category', 'per_page' => 20])->toArray()['data'];

        $filtered = $this->filterCategoriesByPurchases($tags);

        return array_values($filtered);
    }

    /**
     * Filter specified tags by current user envato purchases.
     *
     * @param Tag[] $tags
     * @return array
     */
    private function filterCategoriesByPurchases($tags)
    {
        $user = Auth::user();

        if ( ! $this->settings->get('envato.enable') || $user->isSuperAdmin()) return $tags;

        $names = $user->purchase_codes->pluck('item_name');

        return array_filter($tags, function($tag) use($names) {
            return $names->contains($tag['name']);
        });
    }
}
