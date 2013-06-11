Element.addMethods({

	patchwork: function( element, options ){

		options = Object.extend({
			minSize: 180,
			optimumSize: 200,
			gutterSize: 10,
			minRowCount: 2,
			maxRowCount: 5,
			switchImages: true,
			switchInterval: 8,
			showRandomImage: true
		}, options);

		var boxDims = {},
			curColumns = null,
			curRows = null,
			columnCount,
			itemWidth,
			grid = [],
			maxBigImages,
			images = [],
			totalImages,
			doneInit = false,
			origImageData,
			rowCount;

		//------------------------------------
		// Our kinda-constructor
		//------------------------------------
		var _init = function(){

			// copy image data to a variable that we won't
			// change
			origImageData = options.imageData.clone();
			totalImages = options.imageData.length;

			getBoxDims();
			calculateColumnsAndRows();
			_createBlocks();
			resizeWrapper();
			doneInit = true;
		},

		//------------------------------------
		// Creates the grid our images sit on
		//------------------------------------
		_createGrid = function(){
			grid = [];
			for( var i = 0; i < rowCount; i++ ){
				grid[i] = new Array( columnCount );
			} 
		},

		//------------------------------------
		// Randomly positions the larger images
		//------------------------------------
		_allocateLargeImages = function(){

			// Position the large images
			var checkGrid = function(p){
				return grid[ p[0] ][ p[1] ] == null;
			}

			var startX = Math.floor( Math.random() * ( columnCount - 1 ) );
			var startY = Math.floor( Math.random() * ( rowCount - 1 ) );
			var curX = startX;
			var curY = startY;

			// First loop to build each large image
			// Second loop for each row
			// Third loop for each column
			ImageLoop:
			for( var i = 0; i < maxBigImages; i++ ){
				for( var n = 0; n < ( rowCount - 1 ); n++ ){
					for( var m = 0; m < ( grid[ n ].length - 1 ); m++ ){

						// The coords this image would use if it fits
						var possibleCoords = [ 
												[ curY, curX ],
												[ curY + 1, curX ],
												[ curY + 1, curX + 1 ],
												[ curY, curX + 1 ] 
											];

						// Check the coords are available, insert is possible
						if( possibleCoords.all( checkGrid ) ){
							grid[ curY ][ curX ] = 3;
							grid[ curY+1 ][ curX+1 ] = 2;
							grid[ curY ][ curX+1 ] = 2;
							grid[ curY+1 ][ curX ] = 2;
							continue ImageLoop;
						}

						// subtract 2... 1 so it's zero index
						// another because our image is 2 wide so we'll not bother
						// checking the last column - image would never fit anyway.
						if( curX++ == ( columnCount - 2 ) ){ 
							curX = 0;
						}
					}

					// Same as curX loop - subtract 2
					if( curY++ == ( rowCount - 2 ) ){
						curY = 0;
					}
				}
			}

		},

		//------------------------------------
		// Fills small images and inits all
		//------------------------------------
		_fillAndInitImages = function(){

			images = [];

			for( var n = 0; n < rowCount; n++ ){
				for( var m = 0; m < grid[ n ].length; m++ ){
					
					if( grid[ n ][ m ] == null ){
						images.push( 
							new patchworkImage( {
								pos: [ n, m ],
								size: 'small',
								image: getNextImage(),
								fade: !doneInit,
								variance: getRandomVariance()
							} ) 
						);
					} else if( grid[ n ][ m ] == 3 ){
						images.push( 
							new patchworkImage( { 
								pos: [ n, m ],
								size: 'large',
								image: getNextImage(),
								fade: !doneInit,
								variance: getRandomVariance()
							} )
						);
					}

				}
			}

		},

		//------------------------------------
		// Destroys existing images
		//------------------------------------
		destroyImages = function(){
			images.each( function(item){
				item.destroy();
				delete item;
			});

			images = null;
		},

		//------------------------------------
		// Calls methods in sequence that will
		// build our grid and blocks
		//------------------------------------
		_createBlocks = function(){

			maxBigImages = Math.round( Math.sqrt( rowCount * columnCount )  ) - 2;
			if( maxBigImages < 1 ){ maxBigImages = 1; }
			if( maxBigImages > 5 ){ maxBigImages = 5; }

			Debug.write("Total images shown: " + ( rowCount * columnCount ) );
			Debug.write("Max big images to show: " + maxBigImages );

			_createGrid();
			_allocateLargeImages();
			_fillAndInitImages();
		},

		//------------------------------------
		// Sets our wrapper dimensions variable
		//------------------------------------
		getBoxDims = function(){
			boxDims = $( element ).getDimensions();
		},

		//------------------------------------
		// Resizes the main wrapper to fit vertically
		//------------------------------------
		resizeWrapper = function(){
			var height = calculateHeight( itemWidth );

			$(element).setStyle({
				'height': ( ( height * rowCount ) + ( options.gutterSize * ( rowCount - 1 ) ) ) + 'px'
			});
		},

		//------------------------------------
		// Calculate number and size of columns
		//------------------------------------
		calculateColumnsAndRows = function(){
			var columns = parseInt( boxDims.width / options.optimumSize );
			var leftover = boxDims.width % options.optimumSize;

			// If what's left over is bigger than the minimum size,
			// add an extra column. We'll resize the images.
			if( leftover > options.minSize ){
				columns++;
			}

			columnCount = columns;

			// So our total item width is the box size divided by columns
			itemWidth = Math.floor( ( boxDims.width - ( options.gutterSize * ( columnCount - 1 ) ) ) / columnCount );

			// How many rows?
			// Check whether rows * cols is more than
			// the total number of images we have,
			// and decrement as necessary.
			var done = false;
			var cur = options.maxRowCount;

			do {
				var c = ( cur * columnCount );

				if( c < totalImages || cur == options.minRowCount ){
					rowCount = cur;
					done = true;
				} else {
					cur--;
				}
			} while( !done );
		},

		//------------------------------------
		// Returns height value based on width value
		//------------------------------------
		calculateHeight = function( width ){
			return ( width / 4 ) * 3;
		},

		//------------------------------------
		// Returns a random image and then removes it from the array
		// If we're out of elements, we repopulate and start again
		//------------------------------------
		getNextImage = function(){
			if( options.imageData.length === 0 ){
				_resetImageArray();
			}

			if( options.showRandomImage ){
				var toreturn = options.imageData.splice( Math.floor( Math.random() * options.imageData.length ), 1 )[0];

				while( typeof( toreturn ) == 'undefined' )
				{
					toreturn = options.imageData.splice( Math.floor( Math.random() * options.imageData.length ), 1 )[0];
				}
			} else {
				var toreturn = options.imageData.splice( 0, 1 )[0];
			}

			return toreturn;
		},

		getRandomVariance = function(){
			return ( Math.random() * options.switchInterval ).toFixed(3);
		},

		//------------------------------------
		// resets options.imageData to its original value
		//------------------------------------
		_resetImageArray = function(){
			options.imageData = origImageData.clone();
		},

		// Debug function
		outputGrid = function( grid ){
			for( var i = 0; i < grid.length; i++ ){
				var rowOutput = '';
				for( var j = 0; j < grid[i].length; j++ ){
					if( grid[ i ][ j ] == 3 || grid[ i ][ j ] == 2 ){
						rowOutput += "[x]";
					} else if( grid[ i ][ j ] === 1 ) {
						rowOutput += "[o]";
					} else {
						rowOutput += "[ ]";
					}
				}
				Debug.write( rowOutput );
			}
		};

		//------------------------------------
		// Class for each block
		//------------------------------------
		var patchworkImage = Class.create({

			// Constructor
			initialize: function( data ){
				var self = this;
				this.data = data;
				this.parentElement = element;

				// Build inner element (which holds the image)
				this.innerElement = new Element('div');

				// Build outer element (which has a shadow)
				this.outerElement = new Element('div')
										.setStyle({
											'position': 'absolute'
										})
										.addClassName('featured_image')
										.addClassName('clickable')
										.hide()
										.insert( this.innerElement );
				
				// Set the size and position of this block
				this.sizeAndPosition();

				// Set the background image on the inner element
				this.setBackground( this.innerElement );

				// Insert into our grid
				$( this.parentElement ).insert( this.outerElement );

				// Decide whether to fade in or not
				if( this.data.fade ){
					this.outerElement.appear({ delay: Math.random().toFixed(3), afterFinish: function(){
						if( options.switchImages ){
							self.switchImage.bind(self).delay( parseInt(options.switchInterval) + parseInt(self.data.variance) );
						}
					}});
				} else {
					this.outerElement.show();
				}

				this.outerElement.observe( 'click', function(e) {
					window.location = this.data.image.image_url.replace( /&amp;/, '&' );
				}.bindAsEventListener(this) );

				// Listen for resize event
				$( this.parentElement ).on( 'patchwork:resize', this.resizeEvent.bindAsEventListener(this) );
			},

			switchImage: function(){
				
				// Get new image
				this.data.image = getNextImage();
				
				var newDiv = this.setBackground( new Element('div').hide() );

				this.outerElement.insert( newDiv );

				new Effect.Parallel([
					new Effect.Fade( this.innerElement, { sync: true } ),
					new Effect.Appear( newDiv, { sync: true } )
				], {
					duration: 1.5,
					afterFinish: function(){
						this.innerElement.remove();
						this.innerElement = newDiv;
					}.bind(this)
				});

				if( options.switchImages ){
					this.switchImage.bind(this).delay( parseInt(options.switchInterval) + parseInt(this.data.variance) );
				}
			},

			// Sets the size and position of this block
			sizeAndPosition: function(){
				var itemHeight = calculateHeight( itemWidth );

				this.outerElement.setStyle({
					'left': ( this.data['pos'][1] * itemWidth ) + ( this.data['pos'][1] * options.gutterSize ) + 'px',
					'top': ( this.data['pos'][0] * itemHeight ) + ( this.data['pos'][0] * options.gutterSize ) + 'px',
					'width': ( ( this.data.size == 'large' ) ? ( itemWidth * 2 ) + options.gutterSize : itemWidth ) + 'px',
					'height': ( ( this.data.size == 'large' ) ? ( itemHeight * 2 + options.gutterSize ) : itemHeight ) + 'px'
				});

				return this.outerElement;
			},

			// Sets the background image on an element
			setBackground: function( elem ){

				$( elem ).setStyle({
									backgroundImage: 'url(' + this.data.image.med_img + ')',
									backgroundPosition: 'center center',
									backgroundRepeat: 'no-repeat'
								});

				if( this.data.image.thumb_size.w >= itemWidth ){
					$( elem ).setStyle({
						'background-size': 'cover'
					});
				}

				return elem;
			},

			// Prepare to destroy ourselves
			destroy: function(){
				this.outerElement.remove();
			},

			// Catches the resize event fired by outer script
			resizeEvent: function(e){
				this.sizeAndPosition();
			}
		});

		//------------------------------------
		// Window resize event
		//------------------------------------
		Event.observe( window, 'resize', function(){
			curColumns = columnCount;
			curRows = rowCount;
			getBoxDims();
			calculateColumnsAndRows();

			// Number of columns changed?
			if( curColumns != columnCount || curRows != rowCount ){
				destroyImages();
				_resetImageArray();
				_createBlocks();
			}

			resizeWrapper();

			Event.fire( element, 'patchwork:resize' );
		});

		_init();
		return element;
	}

});