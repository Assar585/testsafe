@if(count($combinations) > 0)
	<table class="table table-bordered aiz-table">
		<thead>
			<tr>
				<td class="text-center">
					{{translate('Variant')}}
				</td>
				<td class="text-center">
					{{translate('Variant Price')}}
				</td>
				<td class="text-center" data-breakpoints="lg">
					{{translate('SKU')}}
				</td>
				<td class="text-center" data-breakpoints="lg">
					{{translate('Quantity')}}
				</td>
				<td class="text-center" data-breakpoints="lg">
					{{translate('Photo')}}
				</td>
			</tr>
		</thead>
		<tbody>
			@foreach ($combinations as $key => $combination)
				@php
					$sku = '';
					if (isset($base_sku) && !empty($base_sku)) {
						$sku = $base_sku;
					} else {
						// Improved fallback logic matching the JS implementation
						$name = trim($product_name);
						$words = explode(' ', $name);
						if (count($words) >= 3) {
							$sku = strtoupper($words[0][0] . $words[1][0] . $words[2][0]);
						} elseif (strlen($name) >= 3) {
							$sku = strtoupper(substr($name, 0, 3));
						} else {
							$sku = 'PRD';
						}
					}

					$str = '';
					foreach ($combination as $key => $item) {
						if ($key > 0) {
							$str .= '-' . str_replace(' ', '', $item);
							$sku .= '-' . str_replace(' ', '', $item);
						} else {
							if ($colors_active == 1) {
								$color_name = \App\Models\Color::where('code', $item)->first()->name;
								$str .= $color_name;
								$sku .= '-' . $color_name;
							} else {
								$str .= str_replace(' ', '', $item);
								$sku .= '-' . str_replace(' ', '', $item);
							}
						}
					}
				@endphp
				@if(strlen($str) > 0)
					<tr class="variant">
						<td>
							<label for="" class="control-label">{{ $str }}</label>
						</td>
						<td>
							<input type="number" lang="en" name="price_{{ $str }}" value="{{ $unit_price }}" min="0" step="0.01"
								class="form-control" required>
						</td>
						<td>
							<input type="text" name="sku_{{ $str }}" value="{{ $sku }}" class="form-control">
						</td>
						<td>
							<input type="number" lang="en" name="qty_{{ $str }}" value="10" min="0" step="1" class="form-control"
								required>
						</td>
						<td>
							<div class=" input-group " data-toggle="aizuploader" data-type="image">
								<div class="input-group-prepend">
									<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}
									</div>
								</div>
								<div class="form-control file-amount text-truncate">{{ translate('Choose File') }}</div>
								<input type="hidden" name="img_{{ $str }}" class="selected-files">
							</div>
							<div class="file-preview box sm"></div>
						</td>
					</tr>
				@endif
			@endforeach
		</tbody>
	</table>
@endif