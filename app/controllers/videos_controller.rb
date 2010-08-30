class VideosController < ApplicationController
        def youtube
                feed = RSS::Parser.parse("http://gdata.youtube.com/feeds/api/videos?orderby=updated&vq=university+of+houston")
                render :json => feed.items.to_json
        end
end
