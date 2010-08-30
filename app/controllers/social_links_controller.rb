class SocialLinksController < ApplicationController
  def index
    if params[:social_category_id] 
      social_category = SocialCategory.find(params[:social_category_id])
      render :json => social_category.social_links

    else
      render :json => SocialLink.all
    end
  end
end
