<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>

<?php if ($pager->hasPrevious() || $pager->hasNext()): ?>
	<nav aria-label="Navigazione utenti">
		<ul class="pagination pagination-sm mb-0 shadow-sm rounded">

			<?php if ($pager->hasPrevious()): ?>
				<li class="page-item">
					<a class="page-link border-0 px-3" href="<?= $pager->getFirst() ?>" aria-label="First">
						<i class="fas fa-angle-double-left small"></i>
					</a>
				</li>
				<li class="page-item">
					<a class="page-link border-0 px-3" href="<?= $pager->getPrevious() ?>" aria-label="Previous">
						<i class="fas fa-chevron-left small"></i>
					</a>
				</li>
			<?php else: ?>
				<li class="page-item disabled">
					<span class="page-link border-0 px-3 text-muted"><i class="fas fa-chevron-left small"></i></span>
				</li>
			<?php endif; ?>

			<?php foreach ($pager->links() as $link): ?>
				<li class="page-item <?= $link['active'] ? 'active' : '' ?>">
					<a class="page-link border-0 fw-bold px-3 mx-1 rounded" href="<?= $link['uri'] ?>">
						<?= $link['title'] ?>
					</a>
				</li>
			<?php endforeach; ?>

			<?php if ($pager->hasNext()): ?>
				<li class="page-item">
					<a class="page-link border-0 px-3" href="<?= $pager->getNext() ?>" aria-label="Next">
						<i class="fas fa-chevron-right small"></i>
					</a>
				</li>
				<li class="page-item">
					<a class="page-link border-0 px-3" href="<?= $pager->getLast() ?>" aria-label="Last">
						<i class="fas fa-angle-double-right small"></i>
					</a>
				</li>
			<?php else: ?>
				<li class="page-item disabled">
					<span class="page-link border-0 px-3 text-muted"><i class="fas fa-chevron-right small"></i></span>
				</li>
			<?php endif; ?>

		</ul>
	</nav>
<?php endif; ?>