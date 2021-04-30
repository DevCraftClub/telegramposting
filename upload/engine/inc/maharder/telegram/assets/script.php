<?php

echo <<<JS
<script>
	var dependencyFields = JSON.parse('{$dependencies}'), 
		depID = '#field', 
		parentDependencyFields = $(document).find(depID).first().parent(), 
		dependencyFieldItems = 0, 
		dependencyFieldCount = 0;
	function createItem(num_id, field_id = null, field_value = '', field_source = '', field_name = '') {
    
    	function gen_options(fields, field_id, field_source, field_name) {
    	    let groups = {
    	        post: {
    	            name: 'Новости',
    	            type: 'post',
    	            values: []
    	        },
    	        post_extras: {
    	            name: 'Новости',
    	            type: 'post_extras',
    	            values: []
    	        },
    	        categories: {
    	            name: 'Категории',
    	            type: 'category',
    	            values: []
    	        },
    	        xfields: {
    	            name: 'Доп. поля',
    	            type: 'xfields',
    	            values: []
    	        }
    	    }, html = '';
    	    $.each(fields, function (key, value) {
    	        let 
    	        	val = {
						name: value.name,
						source: value.source,
						field_id: field_id,
						value: value.value,
						selected: false
    	        	},
    	        	val_id = field_id + ':' + field_source + ':' + field_name,
    	        	val_check = val.field_id + ':' + val.source + ':' + val.name
    	        ;
				val.selected = val_id === val_check;
				if (val.source === 'post') groups.post.values.push(val);
				else if (val.source === 'post_extras') groups.post_extras.values.push(val);
				else if (val.source === 'category') groups.categories.values.push(val);
				else if (val.source === 'xfields') groups.xfields.values.push(val);
    	        
    	    });
    	    $.each(groups, function(k, v) {
    	        html += '<optgroup label="' + v.name + ' (' + v.type + ')">';
    	        for (let i = 0, max = v.values.length; i < max; i++) {
    	            let 
    	            	val = v.values[i],
    	            	val_key = val.field_id + ':' + val.source + ':' + val.name,
    	            	selected = (val.selected) ? 'selected' : '',
    	            	id_name = (val.source === 'category') ? 'ID: ' : ''
    	            ;
    	            html += '<option value="' + val_key + '" '+ selected +'>'+val.value+' ' +
    	             		'(' + id_name + val.name + ')</option>';
    	        }
    	        html += '</optgroup>';
    	    });
    	    
    	    
    	    return html;
    	}
	    
		let 
			html = '<div class="ui three column grid fieldItem" data-id="' + num_id + '">' +
		 				'<div class="column">' +
		 					'<input	class="depField" data-source="'+field_source+'"	data-field_id="'+field_id+'" data-name="'+field_name+'" type="text" placeholder="Зависимость" name="field-' + num_id + '" id="field-' + num_id + '" value="' + field_value + '">' +
		 				'</div>' +
		 				'<div class="column">' +
							'<select class="form-control aksDd" id="source-' + num_id + '" data-id="' + num_id + '">';
    							html += gen_options(dependencyFields, num_id, field_source, field_name);
										
		html += `			</select>
						</div>
						<div class="column">
							<div class="ui mini basic icon buttons">
								<div role="button" class="ui green button" data-action="addNewField" title="Добавить новую зависимость">
									<i class="plus icon"></i>
								</div>
								<div role="button" class="ui red button" data-action="delThisField" title="Удалить зависимость">
									<i class="minus icon"></i>
								</div>
							</div>
						</div>
					</div>`;

		return html;
	}

	function createKeyInputs() {
		let html = '<div class="" name="dependencyFields">';
		let keysValue = {}, countFields = 0;
		try {
			keysValue = JSON.parse(atob($(depID).val()));
			countFields = keysValue.length;
		} catch (e) {
			console.log('No field dependencies were set');
			// $.alert({
			// 	title: 'Ошибка!',
			// 	content: 'Не указана зависимость!',
			// });
		}

		if (countFields > 0) {
			for (let i = 0; i < countFields; i++) {
				dependencyFieldItems++;
				dependencyFieldCount++;
				html += createItem(dependencyFieldItems, keysValue[i].field_id, keysValue[i].value, keysValue[i].source, keysValue[i].name);
			}
		} else {
			dependencyFieldItems++;
			dependencyFieldCount++;
			html += createItem(dependencyFieldItems);
		}

		html += '</div>';

		return html;
	}

	function modifyFieldVal() {
		let fields = [];

		// $('.aksDd').dropdown({
		// 	onChange: function(value, text, sel) {
		// 	    let x
		// 	    	field_data = $(this).find('.selected').data(), 
		// 	    	field = $('#field-' + field_data.field_num)
		// 	    ;
		//	    
		// 		$(field).data('source', field_data.source).data('name', field_data.name).data('field_id', value);
		// 		$(field).attr('data-source', field_data.source).attr('data-name', field_data.name).attr('data-field_id', value);
		//		
		// 		console.log(field_data);
		// 	},
		// 	onLabelSelect: function(selectedLabels) {
		// 	    console.log(selectedLabels);
		// 	}
		// });

		$('[name="dependencyFields"] .aksDd').each(function () {
			let 
				thisID = $(this).data('id'), 
				field_data = $('#source-' + thisID).val().split(':'), 
				field_val = $(document).find('#field-' + thisID).first().val()
			;
		
			if (field_data.source === 'other') {
			    let source_split = field_val.split(':'), val_split = source_split[1].split('|');
			    field_data.source = source_split[0];
			    field_data.name = val_split[0];
			    field_val = val_split[1];
			}
			
			fields.push({
			    field_num: field_data[0],
				name: field_data[2],
				source: field_data[1],
				value: field_val,
			});
		});
		$(depID).val(btoa(JSON.stringify(fields)));
	}

	$(() => {
		let inputs = createKeyInputs();
		$(parentDependencyFields).append(inputs);
		$('.dropdown').dropdown();
		$(depID).hide();

		$(document).on('change', '.fieldItem, [data-name="field"]', function () {
			modifyFieldVal();
		});

		$(document).on('input', '.fieldItem', function () {
			modifyFieldVal();
		});

		$(document).on('click', '[data-action="addNewField"]', function () {
			dependencyFieldItems++;
			dependencyFieldCount++;
			let item = createItem(dependencyFieldItems);
			$('[name="dependencyFields"]').append(item);
			modifyFieldVal();
		});

		$(document).on('click', '[data-action="delThisField"]', function () {

			if (dependencyFieldCount > 1) {
				dependencyFieldCount--;

				$(document).find(this).first().parents('.fieldItem').remove();

				modifyFieldVal();
			} else  $.alert({
				title: 'Ошибка!',
				content: 'Нельзя удалять все поля! Хотя-бы одно да должно остаться!',
			});

		});

		
	});
</script>
JS;
