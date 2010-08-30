require 'net/ldap'
class Person < ActiveRecord::Base
def self.ldap_search text, aff

 ldap = Net::LDAP.new
 ldap.host = "directory.uh.edu"
 ldap.port = "389"
 #ldap.auth "cn=Directory Manager", "password"
 treebase = "o=University of Houston"
 
 name_filter = Net::LDAP::Filter.eq( "cn", "*#{text}*") 
 filtr = name_filter

 if !aff.nil? and (aff == "Staff" or aff == "Faculty" or aff == "Student")
   aff_filter = Net::LDAP::Filter.eq( "affiliation", aff ) 
   filtr = name_filter & aff_filter
 end

 entries = Array.new
 ldap.search( :base => treebase, :filter => filtr ) do |entry|
   dude = {"affiliation" => entry["affiliation"][0],
          "givenname" => entry["givenname"][0],
          "sn" => entry["sn"][0],
          "mail" => entry["mail"][0],
          "title" => entry["title"][0],
          "roomnumber" => entry["roomnumber"][0],
          "buildingname" => entry["buildingname"][0],
          "telephonenumber" => entry["telephonenumber"][0]
     }
   entries << {"person" => dude}
   puts "DN: #{entry.dn}"
   entry.each do |attribute, values|
     print "   #{attribute}:"
     values.each do |value|
       puts "      --->#{value}"
     end
   end
 end


 entries
end 
end
