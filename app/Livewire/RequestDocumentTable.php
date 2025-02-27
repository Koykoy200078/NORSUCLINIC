<?php

namespace App\Livewire;

use App\Models\RequestDocuments;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class RequestDocumentTable extends Component
{
    public $requestDocuments;

    public function mount()
    {
        $this->requestDocuments = RequestDocuments::all();
    }

    public function configure()
    {
        $this->setPrimaryKey('id');
    }

    public function render()
    {
        return view('livewire.request-document-table');
    }
}
