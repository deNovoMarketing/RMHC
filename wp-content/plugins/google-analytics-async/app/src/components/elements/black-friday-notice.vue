<template>
	<notice-black
		:link="$vars.urls.bf"
		:sourceLang="getConfig"
		@onCloseClick="dismiss"
		v-if="canShow"
	>
		<p>
			<strong>{{ $i18n.bf.bold }}</strong>
			{{ $i18n.bf.main }}
		</p>
		<p><small>{{ $i18n.bf.sub }}</small></p>
	</notice-black>
</template>

<script>
import {
	setOption,
	saveOptions,
	isNetworkWide
} from '@/helpers/utils'
import {NoticeBlack} from '@wpmudev/shared-notifications-black-friday'

export default {
	name: 'BlackFridayNotice',

	components: {NoticeBlack},

	computed: {
		/**
		 * Get config options for Black Friday Notice.
		 *
		 * @since 3.3.11
		 * @see https://www.npmjs.com/package/@wpmudev/shared-notifications-black-friday
		 *
		 * @return {*}
		 */
		getConfig() {
			return {
				discount: this.$i18n.bf.discount,
				closeLabel: this.$i18n.bf.close,
				linkLabel: this.$i18n.bf.label,
			}
		},

		/**
		 * Check if we can show BF modal.
		 *
		 * @since 3.3.11
		 *
		 * @return {boolean}
		 */
		canShow() {
			return !!this.$vars.flags.show_bf
		},
	},

	methods: {
		/**
		 * Set the dismiss flag for the notice.
		 *
		 * @since 3.3.11
		 */
		dismiss() {
			const network = isNetworkWide()
			// Set dismiss flag.
			setOption('hide_bf_notice', 'misc', true, network)
			// Save settings.
			saveOptions(network)
		}
	}
}
</script>
