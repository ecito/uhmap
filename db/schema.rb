# This file is auto-generated from the current state of the database. Instead of editing this file, 
# please use the migrations feature of Active Record to incrementally modify your database, and
# then regenerate this schema definition.
#
# Note that this schema.rb definition is the authoritative source for your database schema. If you need
# to create the application database on another system, you should be using db:schema:load, not running
# all the migrations from scratch. The latter is a flawed and unsustainable approach (the more migrations
# you'll amass, the slower it'll run and the greater likelihood for issues).
#
# It's strongly recommended to check this file into your version control system.

ActiveRecord::Schema.define(:version => 20100106122858) do

  create_table "buildings", :force => true do |t|
    t.string   "name"
    t.integer  "number"
    t.string   "address"
    t.string   "description"
    t.float    "latitude"
    t.float    "longitude"
    t.string   "code"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "categories", :force => true do |t|
    t.string   "name"
    t.string   "description"
    t.string   "default_marker"
    t.string   "source"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "interests", :force => true do |t|
    t.string   "name"
    t.string   "marker_icon"
    t.float    "latitude"
    t.float    "longitude"
    t.string   "url"
    t.string   "description"
    t.datetime "created_at"
    t.datetime "updated_at"
    t.string   "picture"
    t.integer  "category_id"
  end

  create_table "routes", :force => true do |t|
    t.integer "agency_id"
    t.string  "route_short_name"
    t.string  "route_long_name"
    t.string  "route_desc"
    t.string  "route_type"
    t.string  "route_url"
    t.string  "route_color"
    t.string  "route_text_color"
  end

  create_table "stop_times", :force => true do |t|
    t.integer "trip_id"
    t.string  "arrival_time"
    t.string  "departure_time"
    t.integer "stop_id"
    t.string  "stop_sequence"
    t.string  "stop_headsign"
    t.string  "pickup_type"
    t.string  "drop_off_type"
    t.string  "shape_dist_traveled"
  end

  create_table "stops", :force => true do |t|
    t.string "stop_name"
    t.string "stop_desc"
    t.string "stop_lat"
    t.string "stop_lon"
    t.string "zone_id"
    t.string "stop_url"
  end

  create_table "trips", :force => true do |t|
    t.integer "route_id"
    t.integer "service_id"
    t.string  "trip_headsign"
    t.integer "direction_id"
    t.integer "block_id"
    t.integer "shape_id"
  end

end
