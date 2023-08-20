<?php

namespace App\Services;

use App\Models\Link;
use App\Repositories\LinkRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LinkServices
{
    protected $repository;

    public function __construct(LinkRepository $repository)
    {
        $this->repository = $repository;
    }

    // Função para paginar os links
    public function paginate()
    {
        return $this->repository->paginate();
    }

    // Função para armazenar um novo link
    public function store(array $data)
    {
        $url = $data['url'];
        $slug = $data['slug'] ?? null;

        // Se não houver um slug definido, gera um slug aleatório
        if (!$slug) {
            $slug = $this->createSlug();
        }

        $user = auth()->user();

        // Obtém informações do site usando WebScrapingServices
        $web_site_info = WebScrapingServices::getWebsiteInfo($url);

        // Cria um novo link associado ao usuário
        $link = $user->links()->create([
            'long_link' => $url,
            'slug' => $slug,
            'title' => $web_site_info->title,
            'favicon' => $web_site_info->favicon
        ]);

        return $link;
    }

    public function update(string $id, array $data)
    {
        $user = auth()->user();

        // Verifica se o link pertence ao usuário antes de atualizá-lo
        if (!$link = $this->repository->findLinkByIdAndUser($id, $user->id)) {
            throw new \Exception('Link não encontrado ou não pertence ao usuário.', 404);
        }

        return $this->repository->update($id, $data);
    }

    public function find(string $id)
    {

        $user = auth()->user();

        // Verifica se o link pertence ao usuário
        if (!$link = $this->repository->findLinkByIdAndUser($id, $user->id)) {
            throw new \Exception('Link não encontrado ou não pertence ao usuário.', 404);
        }

        return $link;
    }

    public function metric(string $slug)
    {
        if (!$link = $this->repository->findLinkBySlug($slug)) {
            throw new \Exception('Link não encontrado.', 404);
        }
        $link->hit_counter++;
        $link->save();

        $link->metrics()->create([
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return $link;
    }

    // Função para verificar se um slug já existe
    private function existSlug(string $slug)
    {
        return Link::where('slug', $slug)->exists();
    }

    // Função para criar um slug único
    private function createSlug()
    {
        $slug = Str::random(random_int(6, 8));

        // Caso o slug aleatório já exista, tenta criar novamente.
        if ($this->existSlug($slug)) {
            $slug = $this->createSlug();
        }
        return $slug;
    }

    public function destroy(string $id)
    {
        $user = auth()->user();

        // Verifica se o link pertence ao usuário antes de excluir.
        if (!$this->repository->findLinkByIdAndUser($id, $user->id)) {
            throw new \Exception('Link não encontrado ou não pertence ao usuário.', 404);
        }

        $this->repository->destroy($id);
    }
}
