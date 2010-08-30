class InterestsController < ApplicationController
	before_filter :authenticate, :except => [:index]

	#protected

	def authenticate
	  authenticate_or_request_with_http_basic do |username, password|
   	     username == "admin" && password == "+drw1232"
 	  end
	end
  # GET /interests
  # GET /interests.xml
  def index
	if params[:category] == "Parking"
		`ruby #{RAILS_ROOT}/app/controllers/parking_parse.rb`
		render :file => "#{RAILS_ROOT}/app/controllers/parking_output.json"
	elsif params[:category] == "METRO"
  		render :json => Interest.stops.to_json
  elsif params[:category] == "Photos"
      render :json => Interest.panoramio
	elsif params[:category]
		category_id = Category.find_by_name(params[:category]).id
		render :json => Interest.find_all_by_category_id(category_id).to_json(:methods => :category_name)
	else
		render :json => Interest.all
	end

  end

  def list
    @interests = Interest.all
  end

  def show
    @interest = Interest.find(params[:id])

    respond_to do |format|
      format.html # show.html.erb
      format.xml  { render :xml => @interest }
    end
  end

  # GET /interests/new
  # GET /interests/new.xml
  def new
    @interest = Interest.new

    respond_to do |format|
      format.html # new.html.erb
      format.xml  { render :xml => @interest }
    end
  end

  # GET /interests/1/edit
  def edit
    @interest = Interest.find(params[:id])
  end

  # POST /interests
  # POST /interests.xml
  def create
    @interest = Interest.new(params[:interest])

    respond_to do |format|
      if @interest.save
        flash[:notice] = 'Interest was successfully created.'
        format.html { redirect_to(@interest) }
        format.xml  { render :xml => @interest, :status => :created, :location => @interest }
      else
        format.html { render :action => "new" }
        format.xml  { render :xml => @interest.errors, :status => :unprocessable_entity }
      end
    end
  end

  # PUT /interests/1
  # PUT /interests/1.xml
  def update
    @interest = Interest.find(params[:id])

    respond_to do |format|
      if @interest.update_attributes(params[:interest])
        flash[:notice] = 'Interest was successfully updated.'
        format.html { redirect_to(@interest) }
        format.xml  { head :ok }
      else
        format.html { render :action => "edit" }
        format.xml  { render :xml => @interest.errors, :status => :unprocessable_entity }
      end
    end
  end

  # DELETE /interests/1
  # DELETE /interests/1.xml
  def destroy
    @interest = Interest.find(params[:id])
    @interest.destroy

    respond_to do |format|
      format.html { redirect_to(interests_url) }
      format.xml  { head :ok }
    end
  end
end
