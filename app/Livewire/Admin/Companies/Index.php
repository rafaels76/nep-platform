<?php

namespace App\Livewire\Admin\Companies;

use App\Models\Company;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $term = trim($this->search);

        $companies = Company::query()
            ->withCount(['users', 'cases'])
            ->when($term !== '', fn ($query) => $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('legal_name', 'like', "%{$term}%")
                    ->orWhere('tax_id', 'like', "%{$term}%");
            }))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.companies.index', ['companies' => $companies]);
    }
}