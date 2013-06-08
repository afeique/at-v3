function drawFrontpageClock() {
    var canvas = document.getElementById('frontpage-clock-canvas');
    if (canvas.getContext) {
        var context = canvas.getContext('2d');
        context.clearRect(0, 0, 300, 300);

        // gradients, fonts, setup
        var innerStrokeGradient = context.createRadialGradient(150, 75, 30, 150, 75, 150);
        var outerStrokeGradient = context.createRadialGradient(150, 75, 50, 150, 75, 150);
        innerStrokeGradient.addColorStop(0, '#3d3d3d');
        innerStrokeGradient.addColorStop(1, '#aaa');
        outerStrokeGradient.addColorStop(0, '#3d3d3d');
        outerStrokeGradient.addColorStop(1, '#000');
        context.font = 'Bold 20px Arial';
        context.textBaseline = 'middle';
        context.textAlign = 'center';

        // common stroke properties of both bezels
        context.lineWidth = 3;
        context.shadowOffsetX = 2;
        context.shadowOffsetY = 2;
        context.shadowBlur = 3;
        context.shadowColor = 'rgba(0,0,0, .3)';

        // inner stroke of outer bezel
        context.strokeStyle = innerStrokeGradient;
        context.lineWidth = 3;
        context.beginPath();
        context.arc(150, 75, 57, 0, Math.PI*2, true);
        context.stroke();

        // outer stroke of outer bezel
        context.strokeStyle = outerStrokeGradient;
        context.beginPath();
        context.arc(150, 75, 60, 0, Math.PI*2, true);
        context.stroke();

        // draw the different hands
        var drawHand = function(length, width, percentOffset, rot) {
            // save existing draw context onto the stack
            context.save();

            // rotate the whole hand as necessary
            context.translate(canvas.width / 2, canvas.height / 2);
            context.rotate(rot);

            /**
             * We're going to draw an elongated triangular path (read: incredibly obtuse triangle),
             * fill it, and transform the triangle (mirror it about the x-axis) to obtain our filled
             * path. To finish, we'll add some shadow and stroke it. (yeah, baby)
             */

            // make some calculations... calculate the triangle's leftmost angle
            var height = width / 2;
            var offset = (percentOffset/100) * length;
            
            var tanAlpha = height / offset;
            var alpha = Math.atan(tanAlpha);

            // create the path to fill
            context.beginPath();

            // draw half the hand, just a triangle
            var drawHalf = function() {
                context.moveTo(0,0);
                context.lineTo(offset, Math.sin(alpha) * height);
                context.lineTo(length, 0);
                context.lineTo(0,0);
                context.closePath();
            }

            // draw half the hand, transform it, then draw the other half
            drawHalf();
            context.scale(1,-1);
            drawHalf();

            var gradient = context.createLinearGradient(0,0,length,height);
            gradient.addColorStop(0,'#4d4d4d');
            gradient.addColorStop(1,'#1d1d1d');
            context.fillStyle = gradient;
            context.fill();

            // setup stroke and shadow

            // add shadow
            context.shadowBlur = 1;
            context.shadowOffsetX = 1;
            context.shadowOffsetY = 1;
            context.shadowColor = 'rgba(0,0,0, .3)';

            context.restore();
        }

        // current time
        var now = new Date();
        drawHand(51, 10, 12, Math.PI/30*now.getSeconds());
        drawHand(23, 7, 12, Math.PI/500*(now.getMilliseconds()-250));

        setTimeout(drawFrontpageClock, 1);
    }
}

drawFrontpageClock();