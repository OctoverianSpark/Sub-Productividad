<?php

namespace Models;

class Paginacion
{

    public static function paginar($modelo, $page = 1, $perPage = 10, $filtros = [])
    {
        $offset = ($page - 1) * $perPage;

        // Construir query base
        $whereClause = "";
        if (!empty($filtros['column']) && !empty($filtros['param'])) {
            $column = $filtros['column'];
            $param = $filtros['param'];
            $whereClause = "WHERE $column LIKE '%$param%'";
        }
        // Query para contar total de registros
        $countQuery = "SELECT COUNT(*) as total FROM " . $modelo::getTabla() . " $whereClause";
        $totalResult = $modelo::consultarSQL($countQuery);
        $totalRecords = $totalResult[0]->total ?? 0;

        // Query para obtener registros paginados
        $dataQuery = "SELECT * FROM " . $modelo::getTabla() . " $whereClause ORDER BY id DESC LIMIT $perPage OFFSET $offset";
        $data = $modelo::consultarSQL($dataQuery);

        // Calcular información de paginación
        $totalPages = ceil($totalRecords / $perPage);

        return [
            'data' => $data,
                'current_page' => $page,
                'per_page' => $perPage,
                'total_records' => $totalRecords,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1,
                'next_page' => $page < $totalPages ? $page + 1 : null,
                'prev_page' => $page > 1 ? $page - 1 : null
        ];
    }

    public static function buildPaginationLinks($baseUrl, $paginacion, $filtros = [])
    {

        $links = [];
        $currentPage = (int)($paginacion['current_page']);
        
        $totalPages = $paginacion['total_pages'];
          $perPage = $paginacion['per_page'];

        
        $hasQuery = (strpos($baseUrl, '?') !== false);
        $glue = $hasQuery ? '&' : '?';

        
        $buildUrl = function ($page) use ($baseUrl, $glue, $filtros, $perPage) {
            $queryParams = array_merge($filtros, [
                'page' => $page,
                'per_page' => $perPage,
            ]);
            return $baseUrl . $glue . http_build_query($queryParams);
        };
        // Primera página
        if ($currentPage > 1) {
            $links['first'] = $buildUrl(1);
        }

        // Página anterior
        if (!empty($paginacion['has_prev']) && !empty($paginacion['prev_page'])) {
            $links['prev'] = $buildUrl($paginacion['prev_page']);
        }

        // Páginas numéricas
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);

        for ($i = $start; $i <= $end; $i++) {
            $links['pages'][$i] = [
                'url' => $buildUrl($i),
                'is_current' => ($i === $currentPage),
            ];
        }

        // Página siguiente
        if (!empty($paginacion['has_next']) && !empty($paginacion['next_page'])) {
            $links['next'] = $buildUrl($paginacion['next_page']);
        }

        // Última página
        if ($currentPage < $totalPages && $totalPages > 0) {
            $links['last'] = $buildUrl($totalPages);
        }
        return $links;
    }
}