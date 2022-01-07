<?php

namespace Webkul\RestApi\Http\Controllers\V1\Admin\CMS;

use Illuminate\Http\Request;
use Webkul\CMS\Repositories\CmsRepository;
use Webkul\Core\Http\Requests\MassDestroyRequest;
use Webkul\RestApi\Http\Resources\V1\Admin\CMS\CMSResource;

class PageController extends CMSController
{
    /**
     * Repository class name.
     *
     * @return string
     */
    public function repository()
    {
        return CmsRepository::class;
    }

    /**
     * Resource class name.
     *
     * @return string
     */
    public function resource()
    {
        return CMSResource::class;
    }

    /**
     * To store a new page in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'url_key'      => ['required', 'unique:cms_page_translations,url_key', new \Webkul\Core\Contracts\Validations\Slug],
            'page_title'   => 'required',
            'channels'     => 'required',
            'html_content' => 'required',
        ]);

        $page = $this->getRepositoryInstance()->create($request->all());

        return response([
            'data'    => new CMSResource($page),
            'message' => __('rest-api::app.response.success.create', ['name' => 'Page']),
        ]);
    }

    /**
     * To update the previously created page in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $locale = core()->getRequestedLocaleCode();

        $request->validate([
            $locale . '.url_key'      => ['required', new \Webkul\Core\Contracts\Validations\Slug, function ($attribute, $value, $fail) use ($id) {
                if (! $this->getRepositoryInstance()->isUrlKeyUnique($id, $value)) {
                    $fail(__('rest-api::app.response.error.already-taken', ['name' => 'Page']));
                }
            }],
            $locale . '.page_title'   => 'required',
            $locale . '.html_content' => 'required',
            'channels'                => 'required',
        ]);

        $page = $this->getRepositoryInstance()->update($request->all(), $id);

        return response([
            'data'    => new CMSResource($page),
            'message' => __('rest-api::app.response.success.update', ['name' => 'Page']),
        ]);
    }

    /**
     * To mass delete the resource from storage.
     *
     * @param  \Webkul\Core\Http\Requests\MassDestroyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function massDestroy(MassDestroyRequest $request)
    {
        foreach ($request->indexes as $index) {
            $this->getRepositoryInstance()->findOrFail($index);

            $this->getRepositoryInstance()->delete($index);
        }

        return response([
            'message' => __('rest-api::app.response.success.mass-operations.delete', ['name' => 'CMS Pages']),
        ]);
    }
}