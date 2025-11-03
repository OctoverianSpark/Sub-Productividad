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
        $currentPage = $paginacion['current_page'];
        $totalPages = $paginacion['total_pages'];
        $perPage = $paginacion["per_page"];

        $params = http_build_query($filtros);
        $separator = !empty($params) ? '&' : '';

        $whereClause = "";
        if (!empty($filtros["column"]) && !empty($filtros["param"])){
            $column = $filtros["column"]; 
            $param  = $filtros["param"];
            $whereClause = "column=$column&param=$param";  
        }

        // Primera página
        if ($currentPage > 1) {
            $links['first'] = $baseUrl . '?' . $whereClause . $separator . 'page=1' . $separator . "per_page=" . $perPage;
        }

        // Página anterior
        if ($paginacion['has_prev']) {
            $links['prev'] = $baseUrl . '?' . $whereClause . $separator . 'page=' . $paginacion['prev_page'] . $separator . "per_page=" . $perPage;
        }

        // Páginas numéricas 
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);

        for ($i = $start; $i <= $end; $i++) {
            $links['pages'][$i] = [
                'url' => $baseUrl . '?' . $whereClause . $separator . 'page=' . $i . $separator . "per_page=" . $perPage,
                'is_current' => $i == $currentPage
            ];
        }

        // Página siguiente
        if ($paginacion['has_next']) {
            $links['next'] = $baseUrl . '?' . $whereClause . $separator . 'page=' . $paginacion['next_page'] . $separator . "per_page=" . $perPage;
        }

        // Última página
        if ($currentPage < $totalPages) {
            $links['last'] = $baseUrl . '?' . $whereClause . $separator . 'page=' . $totalPages . $separator . "per_page=" . $perPage;
        }
        return $links;
    }
}
