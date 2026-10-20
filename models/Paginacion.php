<?php

namespace Models;

class Paginacion
{
    public static function buildPaginationLinks(string $baseUrl, array $paginacion, array $filtros = []): array
    {
        $links = [];
        $currentPage = (int) $paginacion["current_page"];
        $totalPages = (int) $paginacion["total_pages"];
        $perPage = $paginacion["per_page"];

        $glue = str_contains($baseUrl, "?") ? "&" : "?";

        $buildUrl = function ($page) use ($baseUrl, $glue, $filtros, $perPage) {
            $queryParams = array_merge(array_filter($filtros, fn($v) => $v !== "" && $v !== null), [
                "page" => $page,
                "per_page" => $perPage,
            ]);
            return $baseUrl . $glue . http_build_query($queryParams);
        };

        // Primera página
        if ($currentPage > 1) {
            $links["first"] = $buildUrl(1);
        }

        // Página anterior
        if (!empty($paginacion["has_prev"]) && !empty($paginacion["prev_page"])) {
            $links["prev"] = $buildUrl($paginacion["prev_page"]);
        }

        // Páginas numéricas
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);

        for ($i = $start; $i <= $end; $i++) {
            $links["pages"][$i] = [
                "url" => $buildUrl($i),
                "is_current" => ($i === $currentPage),
            ];
        }

        // Página siguiente
        if (!empty($paginacion["has_next"]) && !empty($paginacion["next_page"])) {
            $links["next"] = $buildUrl($paginacion["next_page"]);
        }

        // Última página
        if ($currentPage < $totalPages && $totalPages > 0) {
            $links["last"] = $buildUrl($totalPages);
        }
        return $links;
    }
}
