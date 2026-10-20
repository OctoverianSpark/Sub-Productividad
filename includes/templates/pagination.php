 <!-- Incluir controles de paginación -->
    <?php if (isset($paginacion) && $paginacion['total_pages'] > 0 ): ?>
        <div class="pagination-container">
            <!-- Información de registros -->
            <div class="pagination-info">
                <p>Página <?php echo (int) $paginacion['current_page']; ?> de <?php echo (int) $paginacion['total_pages']; ?></p>
            </div>

            <!-- Controles de paginación -->
            <nav class="pagination-nav" aria-label="Navegación de páginas">
                <ul class="pagination-list">

                    <!-- Primera página -->
                    <?php if (isset($pagination_links['first'])): ?>
                        <li class="pagination-item">
                            <a href="<?php echo s($pagination_links['first']); ?>" class="pagination-link pagination-first" title="Primera página">
                                <i class='bx bx-chevrons-left'></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Página anterior -->
                    <?php if (isset($pagination_links['prev'])): ?>
                        <li class="pagination-item">
                            <a href="<?php echo s($pagination_links['prev']); ?>" class="pagination-link pagination-prev" title="Página anterior">
                                <i class='bx bx-chevron-left'></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Páginas numéricas -->
                    <?php if (isset($pagination_links['pages'])): ?>
                        <?php foreach ($pagination_links['pages'] as $page => $pageData): ?>
                            <li class="pagination-item">
                                <?php if ($pageData['is_current']): ?>
                                    <span class="pagination-link pagination-current" aria-current="page">
                                        <?php echo (int) $page; ?>
                                    </span>
                                <?php else: ?>
                                    <a href="<?php echo s($pageData['url']); ?>" class="pagination-link" title="Página <?php echo (int) $page; ?>">
                                        <?php echo (int) $page; ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Página siguiente -->
                    <?php if (isset($pagination_links['next'])): ?>
                        <li class="pagination-item">
                            <a href="<?php echo s($pagination_links['next']); ?>" class="pagination-link pagination-next" title="Página siguiente">
                                <i class='bx bx-chevron-right'></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Última página -->
                    <?php if (isset($pagination_links['last'])): ?>
                        <li class="pagination-item">
                            <a href="<?php echo s($pagination_links['last']); ?>" class="pagination-link pagination-last" title="Última página">
                                <i class='bx bx-chevrons-right'></i>
                            </a>
                        </li>
                    <?php endif; ?>

                </ul>
            </nav>

            <!-- Selector de registros por página -->
            <div class="pagination-per-page">
                <form method="get" class="per-page-form">
                    <!-- Mantener filtros actuales -->
                    <?php foreach (["table", "column", "param", "from", "to"] as $filtro): ?>
                        <?php if (!empty($_GET[$filtro]) && is_string($_GET[$filtro])): ?>
                            <input type="hidden" name="<?php echo $filtro ?>" value="<?php echo s($_GET[$filtro]); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <label for="per_page">Registros por página:</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()">
                        <?php foreach (\MVC\Config::get("paginacion.por_pagina_permitidos", [20, 50, 100]) as $opcion): ?>
                            <option value="<?php echo (int) $opcion ?>" <?php echo ($paginacion['per_page'] == $opcion) ? 'selected' : ''; ?>><?php echo (int) $opcion ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
    <?php endif; ?>
