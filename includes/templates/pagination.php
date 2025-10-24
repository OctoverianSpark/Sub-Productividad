 <!-- Incluir controles de paginación para empleados -->
    <?php if (isset($paginacion) && $paginacion['total_pages'] > 0 ): ?>
        <div class="pagination-container">
            <!-- Información de registros -->
            <div class="pagination-info">
                <p>Página <?php echo $paginacion['current_page']; ?> de <?php echo $paginacion['total_pages']; ?></p>
            </div>
            
            <!-- Controles de paginación -->
            <nav class="pagination-nav" aria-label="Navegación de páginas">
                <ul class="pagination-list">
                    
                    <!-- Primera página -->
                    <?php if (isset($pagination_links['first'])): ?>
                        <li class="pagination-item">
                            <a href="<?php echo $pagination_links['first']; ?>" class="pagination-link pagination-first" title="Primera página">
                                <i class='bx bx-chevrons-left'></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Página anterior -->
                    <?php if (isset($pagination_links['prev'])): ?>
                        <li class="pagination-item">
                            <a href="<?php echo $pagination_links['prev']; ?>" class="pagination-link pagination-prev" title="Página anterior">
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
                                        <?php echo $page; ?>
                                    </span>
                                <?php else: ?>
                                    <a href="<?php echo $pageData['url']; ?>" class="pagination-link" title="Página <?php echo $page; ?>">
                                        <?php echo $page; ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Página siguiente -->
                    <?php if (isset($pagination_links['next'])): ?>
                        <li class="pagination-item">
                            <a href="<?php echo $pagination_links['next']; ?>" class="pagination-link pagination-next" title="Página siguiente">
                                <i class='bx bx-chevron-right'></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Última página -->
                    <?php if (isset($pagination_links['last'])): ?>
                        <li class="pagination-item">
                            <a href="<?php echo $pagination_links['last']; ?>" class="pagination-link pagination-last" title="Última página">
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
                    <?php if (!empty($_GET['table'])): ?>
                        <input type="hidden" name="table" value="<?php echo htmlspecialchars($_GET['table']); ?>">
                    <?php endif; ?>
                    <?php if (!empty($_GET['column'])): ?>
                        <input type="hidden" name="column" value="<?php echo htmlspecialchars($_GET['column']); ?>">
                    <?php endif; ?>
                    <?php if (!empty($_GET['param'])): ?>
                        <input type="hidden" name="param" value="<?php echo htmlspecialchars($_GET['param']); ?>">
                    <?php endif; ?>
                    <?php if (!empty($_GET['from'])): ?>
                        <input type="hidden" name="from" value="<?php echo htmlspecialchars($_GET['from']); ?>">
                    <?php endif; ?>
                    <?php if (!empty($_GET['to'])): ?>
                        <input type="hidden" name="to" value="<?php echo htmlspecialchars($_GET['to']); ?>">
                    <?php endif; ?>
                    
                    <label for="per_page">Registros por página:</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()">
                        <option value="20" <?php echo ($paginacion['per_page'] == 20) ? 'selected' : ''; ?>>20</option>
                        <option value="50" <?php echo ($paginacion['per_page'] == 50) ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo ($paginacion['per_page'] == 100) ? 'selected' : ''; ?>>100</option>
                    </select>
                </form>
            </div>
        </div>
    <?php endif; ?>