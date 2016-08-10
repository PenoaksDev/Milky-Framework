<?php

/*
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

namespace Milky\Config\Builder;

interface ConfigurationSection
{
	public function key();
	public function toArray();
	public function toPhp();
	public function getPhpDoc();
	public function hasPhpDoc();
}
