var segment_write_key = '*** UPDATE WRITE KEY ***';

const SegmentSpark = {
  install: function(Vue, options) {
    Vue.mixin({
    	mounted: function () {
    		!function(){var analytics=window.analytics=window.analytics||[];if(!analytics.initialize)if(analytics.invoked)window.console&&console.error&&console.error("Segment snippet included twice.");else{analytics.invoked=!0;analytics.methods=["trackSubmit","trackClick","trackLink","trackForm","pageview","identify","reset","group","track","ready","alias","debug","page","once","off","on"];analytics.factory=function(t){return function(){var e=Array.prototype.slice.call(arguments);e.unshift(t);analytics.push(e);return analytics}};for(var t=0;t<analytics.methods.length;t++){var e=analytics.methods[t];analytics[e]=analytics.factory(e)}analytics.load=function(t){var e=document.createElement("script");e.type="text/javascript";e.async=!0;e.src=("https:"===document.location.protocol?"https://":"http://")+"cdn.segment.com/analytics.js/v1/"+t+"/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(e,n)};analytics.SNIPPET_VERSION="4.0.0";
			    analytics.load(segment_write_key);
			    analytics.page();
			    if(this.user) {
			    	analytics.identify(this.user.id, this.user);
			    }
			}}();
			if(window.location.href.indexOf("/login") > -1) {
				this.checkHash();
				window.addEventListener("hashchange", this.checkHash);
			};
    	},
    	methods: {
    		checkHash() {
    			switch(location.hash) {
					case '#/profile':
						analytics.page("Settings - Profile");
						break;
					case '#/security':
						analytics.page("Settings - Security");
						break;
					case '#/subscription':
						analytics.page("Settings - Subscription");
						this.watchPlanButtons();
						break;
					case '#/payment-method':
						analytics.page("Settings - Payment Method");
						break;
					case '#/invoices':
						analytics.page("Settings - Invoices");
						break;
				}
    		},
    		watchPlanButtons() {
    			document.getElementsByClassName('btn-plan').addEventListener("click", this.recordAddProduct(this.selectedPlan));
    			document.getElementsByClassName('btn-plan').addEventListener("click", this.completeCheckoutStep(1));
    			document.getElementsByClassName('btn-plan').addEventListener("click", this.viewCheckoutStep(2));

    			this.findButtonbyTextContent("Plan Features").addEventListener("click", this.recordViewProduct(this.detailingPlan));

    			this.findButtonbyTextContent("Subscribe").addEventListener("click", this.completeCheckoutStep(2));
    		},
    		findButtonbyTextContent(text) {
				var buttons = document.querySelectorAll('button');
				for (var i=0, l=buttons.length; i<l; i++) {
					if (buttons[i].firstChild.nodeValue == text)
						return buttons[i];
				}  
			},
    		viewCheckoutStep(step_number) {
    			analytics.track('Viewed Checkout Step', {
				  step: step_number
				});
			},
			completeCheckoutStep(step_number) {
    			analytics.track('Completed Checkout Step', {
				  step: step_number
				});
			},
			recordViewProduct(plan) {
				analytics.track('Product Viewed', {
					product_id: plan.id,
					sku: plan.id,
					name: plan.name,
					price: plan.price,
					quantity: 1,
					value: plan.price,
				});
			},
			recordAddProduct(plan) {
				analytics.track('Product Added', {
					product_id: plan.id,
					sku: plan.id,
					name: plan.name,
					price: plan.price,
					quantity: 1,
					value: plan.price,
				});
			},
    	}
    });
  }
}
module.exports = SegmentSpark;