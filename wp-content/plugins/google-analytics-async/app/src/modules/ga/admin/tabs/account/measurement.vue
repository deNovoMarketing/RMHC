<template>
	<fragment>
		<div
			:class="{ 'sui-form-field-error': error }"
			class="sui-form-field beehive-margin-bottom--10"
		>
			<label for="beehive-settings-measurement-id" class="sui-label">
				{{ $i18n.label.measurement_id }}
			</label>
			<!-- Tracking ID input -->
			<measurement-id
				id="beehive-settings-measurement-id"
				v-model="measurement"
				@validation="handleValidation"
			/>
		</div>
		<sui-notice v-if="error" type="error">
			<p v-html="$i18n.notice.invalid_measurement_id"></p>
		</sui-notice>
		<sui-notice type="default">
			<p v-html="$i18n.notice.measurement_id"></p>
		</sui-notice>
	</fragment>
</template>

<script>
import MeasurementId from './fields/measurement-id'
import SuiNotice from '@/components/sui/sui-notice'

export default {
	name: 'Measurement',

	components: {
		SuiNotice,
		MeasurementId,
	},

	props: {
		error: {
			type: Boolean,
			default: false,
		},
	},

	computed: {
		/**
		 * Computed model object for measurement id input.
		 *
		 * @since 3.3.3
		 *
		 * @returns {string}
		 */
		measurement: {
			get() {
				return this.getOption('measurement', 'tracking', '')
			},
			set(value) {
				this.setOption('measurement', 'tracking', value)
			},
		},
	},

	methods: {
		/**
		 * Handle the input validation event.
		 *
		 * @param {boolean} valid Validation status.
		 *
		 * @since 3.3.0
		 */
		handleValidation(valid) {
			// Emit to parent.
			this.$emit('validation', valid)
		},
	},
}
</script>
