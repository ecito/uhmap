class SocialCategoriesController < ApplicationController
  def index
    render :json => SocialCategory.all
  end

  def show
    render :text => "sup"
  end
end
