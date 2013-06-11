/************************************************/
/* IPB3 Javascript								*/
/* -------------------------------------------- */
/* ips.gallery_slider.js - Gallery image slider	*/
/* (c) IPS, Inc 2009							*/
/* -------------------------------------------- */
/* Author: Brandon Farber						*/
/************************************************/

var ipb_gallery = { };

ipb_gallery.carousel = Class.create({
	
	hLeft			: false,
	hRight			: false,
	wrapper			: null,
	items			: null,
	active			: null,
	autoProgress	: null,
	
	/**
	 * Initialize the carousel
	 */
	initialize: function( wrapper, options )
	{
		this.wrapper = $(wrapper);
		this.options = Object.extend({
			handle: 'feature',
			duration: 5
		}, options || {});

		if( $( this.options.handle + '_left' ) ){
			this.hLeft	= $( this.options.handle + '_left' );
		}

		if( $( this.options.handle + '_right' ) ){
			this.hRight	= $( this.options.handle + '_right' );
		}

		try
		{
			this.items	= this.wrapper.select(".carousel_img");
		}
		catch( e )
		{
			return false;
		}

		this.items.each( function(elem, index){
			if( index > 0 )
			{
				$(elem).hide();
			}
		});

		this.active	= this.items.detect(function(n){ return $(n).visible(); });
		
		$(this.wrapper).on( 'mouseenter', this.mouseEnter.bindAsEventListener(this) );
		$(this.wrapper).on( 'mouseleave', this.mouseLeave.bindAsEventListener(this) );
		$(this.wrapper).on( 'click', 'a.carousel_nav', this.mouseClick.bind(this) );
		
		if( this.hLeft && this.hRight && this.items.length > 1 )
		{
			this.hLeft.show().setStyle('opacity: 0.12');
			this.hRight.show().setStyle('opacity: 0.12');
		}

		if ( this.items.length > 1)
		{
			this.startAutoProgress();
		}
	},

	/**
	 * Callback for when a user mouses over carousel
	 */
	mouseEnter: function(e)
	{
		if( this.hLeft && this.hRight )
		{
			new Effect.Morph( this.hLeft, { 'style':'opacity: 1;', duration: 0.3 } );
			new Effect.Morph( this.hRight, { 'style':'opacity: 1;', duration: 0.3 } );
		}
		
		clearTimeout( this.autoProgress );
	},

	/**
	 * Callback for when a user mouses out of carousel
	 */
	mouseLeave: function(e)
	{
		if( this.hLeft && this.hRight )
		{
			new Effect.Morph( this.hLeft, { 'style':'opacity: 0.12;', duration: 0.3 } );
			new Effect.Morph( this.hRight, { 'style':'opacity: 0.12;', duration: 0.3 } );
		}
		
		if ( this.items.length > 1)
		{
			this.startAutoProgress();
		}
	},

	/**
	 * Callback for when a user clicks on the next/prev buttons
	 */
	mouseClick: function(e, element)
	{
		Event.stop(e);

		if( $(element).hasClassName('carousel_right') )
		{
			this.updatePane( this.getNext( this.active ) );
		}
		else if( $(element).hasClassName('carousel_left') )
		{
			this.updatePane( this.getPrev( this.active ) );
		}
	},

	/**
	 * Initialize carousel auto-progression
	 */
	startAutoProgress: function()
	{
		this.autoProgress = setTimeout( function(){ 
			this.updatePane( this.getNext( this.active ) );
			this.startAutoProgress();
		}.bind(this), this.options.duration * 1000 );		
	},

	/**
	 * Update the displayed image
	 */
	updatePane: function( newPane )
	{
		new Effect.Fade( $( this.active ), { duration: 0.5 } );
		new Effect.Appear( $( newPane ), { duration: 0.5 } );
		
		this.active = newPane;
	},

	/**
	 * Retrieve the next image
	 */
	getNext: function( cur )
	{
		// If no ID or currentImage is specified, return first image
		if( Object.isUndefined( cur ) ){
			return this.items.first();
		}
		
		var pos = this.items.indexOf( cur );
		
		// Last item?
		if( pos == ( this.items.length - 1 ) )
		{
			return this.items.first();
		}
		else
		{
			return this.items[ pos + 1 ];
		}
	},

	/**
	 * Retrieve the previous image
	 */
	getPrev: function( cur )
	{
		// If no ID or currentImage is specified, return first image
		if( Object.isUndefined( cur ) ){
			return this.items.first();
		}

		var pos = this.items.indexOf( cur );

		// Last item?
		if( pos === 0 )
		{
			return this.items.last();
		}
		else
		{
			return this.items[ pos - 1 ];
		}
	}
});