<?php

namespace TopviewDigital\Extension\Form;

use Encore\Admin\Form;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\MessageBag;
use Illuminate\Contracts\Support\MessageProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
class ModalForm extends Form
{
    /**
     * Create a new form instance.
     *
     * @param $model
     * @param \Closure $callback
     */
    public function __construct($model, Closure $callback = null)
    {
        parent::__construct($model, $callback);
        $this->builder->setView('admin-ext::form.modal-form');
        $this->disableSubmit();
        $this->disableReset();
    }

    /**
     * Parse the given errors into an appropriate value.
     *
     * @param  \Illuminate\Contracts\Support\MessageProvider|array|string  $provider
     * @return \Illuminate\Support\MessageBag
     */
    protected function parseErrors($provider)
    {
        if ($provider instanceof MessageProvider) {
            return $provider->getMessageBag();
        }

        return new MessageBag((array) $provider);
    }

    /**
     * Flash a container of errors to the session.
     *
     * @param  \Illuminate\Contracts\Support\MessageProvider|array|string  $provider
     * @param  string  $key
     * @return $this
     */
    public function withErrors($provider, $key = 'default')
    {
        $value = $this->parseErrors($provider);

        $errors = request()->session()->get('errors', new ViewErrorBag);

        if (! $errors instanceof ViewErrorBag) {
            $errors = new ViewErrorBag;
        }

        request()->session()->flash(
            'errors', $errors->put($key, $value)
        );

        return $this;
    }

        /**
     * Remove all uploaded files form the given input array.
     *
     * @param  array  $input
     * @return array
     */
    protected function removeFilesFromInput(array $input)
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->removeFilesFromInput($value);
            }

            if ($value instanceof SymfonyUploadedFile) {
                unset($input[$key]);
            }
        }

        return $input;
    }
    /**
     * Flash an array of input to the session.
     *
     * @param  array  $input
     * @return $this
     */
    public function withInput(array $input = null)
    {
        request()->session()->flashInput($this->removeFilesFromInput(
            ! is_null($input) ? $input : request()->input()
        ));

        return $this;
    }

    public function update($id, $data = null)
    {

        if ($validationMessages = $this->validationMessages(request()->all())) {
            $this->withInput()->withErrors($validationMessages);
            return response(['status'=>false,'data'=>'validation'],422);
        }
        try{
            return parent::update($id, $data);
        }catch (\Exception $e){
            return response(['status'=>false,'data'=>$e->getMessage()],422);
        }
    }

    public function store()
    {
        if ($validationMessages = $this->validationMessages(request()->all())) {
            $this->withInput()->withErrors($validationMessages);
            return response(['status'=>false,'data'=>'validation'],422);
        }
        try{
            return parent::store();
        }catch (\Exception $e){
            return response(['status'=>false,'data'=>$e->getMessage()],422);
        }
    }
}
