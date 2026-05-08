s|href="/\([^"]*\)"|href="<?= base_url('\1') ?>"|g
s|action="/\([^"]*\)"|action="<?= base_url('\1') ?>"|g
s|src="/\([^"]*\)"|src="<?= base_url('\1') ?>"|g