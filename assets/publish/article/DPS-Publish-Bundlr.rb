
# Example application to demonstrate some basic Ruby features
     # This code loads a given file into an associated application

$xsd_string = '<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
    <xs:element name="manifest">
        <xs:complexType>
            <xs:sequence>
                <xs:element ref="index" minOccurs="1" maxOccurs="1"/>
                <xs:element ref="overlays" minOccurs="0" maxOccurs="1"/>
                <xs:element ref="resources" minOccurs="0" maxOccurs="1"/>
            </xs:sequence>
            <xs:attribute name="version" use="required" type="xs:string"/>
            <xs:attribute name="targetViewer" use="required" type="xs:string"/>
            <xs:attribute name="dateModified" use="required" type="xs:dateTime"/>
        </xs:complexType>
    </xs:element>
    <xs:element name="index">
        <xs:complexType>
            <xs:attribute name="type" use="required" type="xs:string" />
            <xs:attribute name="href" use="required" type="xs:anyURI" />
        </xs:complexType>
    </xs:element>
    <xs:element name="overlays">
        <xs:complexType>
            <xs:attribute name="href" use="required" type="xs:anyURI" />
        </xs:complexType>
    </xs:element>
    <xs:element name="resource">
        <xs:complexType>
            <xs:attribute name="type" use="required" type="xs:string" />
            <xs:attribute name="href" use="required" type="xs:anyURI" />
            <xs:attribute name="length" use="required" type="xs:unsignedLong" />
            <xs:attribute name="md5" use="required" type="xs:string" />
        </xs:complexType>
    </xs:element>
    <xs:element name="resources">
        <xs:complexType>
            <xs:sequence>
                <xs:element ref="resource" minOccurs="1" maxOccurs="unbounded"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>'

      class ManifestMaker
      end

mm = ManifestMaker.new

class ManifestMaker
	$basePath = ''

  # Execute the given file using the associate app
  def run file_name 
  	require 'digest/md5'
  	require 'rexml/document'

#	puts "Time=#{get_time}"
#	exit

    # create the index and overlays element
    index = REXML::Element.new("index");
    overlays = REXML::Element.new("overlays"); 
    # now do it with LibXML

	if (File.stat(file_name).file?)
		fileName = File.basename(file_name);		
		# if this is an html or xml file use that as the index
		# might want to restrict this to only default to Article.xml and index.html
		# but as the user ran the script pointing to this file we will let them have their way for now
		if File.fnmatch('*.xml', fileName)
			index.add_attribute("type", "application/vnd.adobe.article+xml")
			index.add_attribute("href", fileName)
		elsif File.fnmatch('*.html', file_name)
			index.add_attribute("type", get_file_type(fileName))
			index.add_attribute("href", fileName)
		end
		
		# need to set the basePath directory where the index file sits
		# use slice will modify file-Name to be the path so we can do the check for file_name being a directory
		file_name.slice!(fileName)
	end
    
    if File.stat(file_name).directory?
		# make sure the path ends with a slash ... won't work for Windows ...
		if (! file_name.end_with?(File::SEPARATOR))
			file_name = File.join(file_name, File::SEPARATOR)
		end

  		$basePath = file_name
  		#print "#{file_name} is a directory\n"
  		
  		# create an html file array
  		htmlFiles = Array.new
  		  		
  		# might want to iterate through the top files to determine if static or dynamic content
  		overlaysFile = ''
  		Dir.foreach(file_name) {
  			|x|
  			if File.fnmatch('*', x)
  				if (File.stat(File.join(file_name, x)).file?)
					if (x.downcase == "article.xml")
						if (index.has_attributes?)
							# puts "Already have an index element!"
						else 
							index.add_attribute("type", get_file_type(x))
							index.add_attribute("href", x)
						end
					elsif (x.downcase == "index.html")
						if (index.has_attributes?)
							# puts "Already have an index element!"
						else 
							index.add_attribute("type", get_file_type(x))
							index.add_attribute("href", x)
						end
					elsif (x.downcase == "overlays.xml")
						if (overlays.has_attributes?)
							# puts "Already have an overlays element!"
						else 
							overlays.add_attribute("type", get_file_type(x))
							overlays.add_attribute("href", x)
						end
					else	# add any html file to an array of potential index files
						if File.fnmatch('*.html', x)
							htmlFiles.push(x)
						end
					end
				end
			end
		}
		
		# determine if we have an index entry
  		if ((! index.has_attributes?) && htmlFiles.count)
  			# if not, get html files in the root directory
#  			puts "I didn't find an index.html file but did locate #{htmlFiles.count} other HTML files at the directory root."
  			# puts "Please choose which html file to use as the article index."
  			# puts "[Enter 0 to abort this script]"
#  			puts
  			count = 1;
  			htmlFiles.each {|x| puts "#{count}) #{x}"; count = count+1}
  			indexValue = readInput("Choose which HTML file to use as the index: ");
  			# puts "indexValue: #{indexValue}"
  			indexValue = indexValue.to_i;
  			if ((indexValue > 0) && (indexValue <= htmlFiles.count))
  				# update index
  				x = htmlFiles[indexValue-1]
  				index.add_attribute("type", get_file_type(x))
				index.add_attribute("href", x)
  			else
  				# abort
  				# puts "Exiting ...."
  				exit
  			end		
   		end
  		

  		# create XML document
  		manifest = REXML::Element.new("manifest")
  		
  		manifest.add_attribute("version", "3.0.0")
  		manifest.add_attribute("targetViewer", "33.0.0")
  		manifest.add_attribute("dateModified", get_time)
		# add the index element
  		manifest.add_element(index)
 
  		if (overlays.has_attributes?)
  			manifest.add_element(overlays)
  		end
  		
  		# create the resources element
  		resources = REXML::Element.new("resources")
  		# add resource elements located in the designated folder
  		add_resources_from_dir(file_name, resources)
  		# ad the the resources element to the manifest element
  		manifest.add_element(resources)
  		
  		# create XML document
  		xmlDoc = REXML::Document.new
  		# add the root manifest element
  		xmlDoc.add_element(manifest)
  		  		 
  		# validate the xml
  		xmlIsValid = validate(manifest)		
  		if (xmlIsValid)		
			# write out the xml
			xmlDoc.write(output=$stdout, indent=2, transitive=false)
			
  		end

  	end
  end

	def readInput(message)
		# print message.to_s()
		STDOUT.flush
		option = STDIN.gets.chomp
		inputString = option.strip
		return inputString
	end
	
	def confirm(message)
		require 'io/console'
		
		# print message.to_s()+" (y/n) "
		STDOUT.flush
		char = STDIN.getch
		# puts	# to move to the next line on the console
		# determine if char is y or Y
		if ((char == 'y') || (char == 'Y'))
			return true
		else
			return false
		end
	end

	def validate(manifest)
		require 'libxml'
		
		parser = LibXML::XML::Parser.string(manifest.to_s)
		document = parser.parse
		schema = LibXML::XML::Schema.from_string($xsd_string)
		result = document.validate_schema(schema)
		# puts "Schema Validation Results: #{result}"
		return result
	end
	
	def add_resources_from_dir(path, resources, level=0)
  		#require 'digest/md5'
  		#require 'openssl'
  		require 'rexml/document'
  		
  		# puts "in add_resources_from_dir level: #{level}"
  		
		Dir.foreach(path) {
  			|x| # puts "#{x}"
  			# make sure the name doesn't begin with '.'
  			# fnmatch will not include files that begin with a '.'
  			if File.fnmatch('*', x)
  			#	puts "Valid file: #{x}"
  				newPath = File.join(path, x);
				if (File.stat(newPath).directory?)
					add_resources_from_dir(newPath, resources, level+1)
				elsif ((x.downcase == "manifest.xml") && (level < 1))
					# puts "Found a manifest.xml file in the root directory, skipping it ...."
					# other approaches could be to check at the script start and if found delete it ... I don't like that approach
					# another is to check the href for a manifest file in the base path - see comments later in this method
				elsif (File.stat(newPath).file?)
					length = File.stat(newPath).size
					type = get_file_type(x)
					href = get_href(newPath.dup)	# need to pass a dup of the str object or else I corrupt the newPath obj
					md5 = get_md5(newPath)
					#puts "#{newPath} href=#{href} type=#{type} length=#{length} md5=#{md5}"
					resource = REXML::Element.new("resource")
					resource.add_attribute("type", type)
					resource.add_attribute("href", href)
					resource.add_attribute("length", length)
					resource.add_attribute("md5", md5)
					# I could check if href doesn't equals manifest.xml to skip it, rather than checking the recursion level
					# if (href.downcase != "manifest.xml")
					resources.add_element(resource)
				end
  			end
  		}		
	end

	def get_href(filePath)
		filePath.slice!($basePath)
		return filePath
	end
	
	def get_md5(path, base64=true)
		require 'digest/md5'
				
		buffer = ''
		
		file = File.open(path, "r")
		while (line = file.gets)
			buffer += line;
		end
		if base64
			digest = Digest::MD5.base64digest(buffer)
			return digest.to_s
		else
			digest = Digest::MD5.hexdigest(buffer)
			return digest.to_s
		end
	end
	
	def get_time()
		require 'time'
		#t = Time.utc()
		t = Time.now
		t.round.iso8601(0)
		#p t
		#p t.round.iso8601(0)

#		require 'date'
		#puts Date.gregorian.to_s
		
#		d = Date.new(Time.now,Date::GREGORIAN)
#		p d
#		d.new_start(Date::GREGORIAN)
#		p d
	end

	# Return the part of the file name string after the last '.'
	def get_file_type(file_name)
		if (file_name.downcase == "article.xml")
			return "application/vnd.adobe.article+xml"
		end # need a check for index.html as well ....
		ext = File.extname( file_name ).gsub( /^\./, '' ).downcase
		extension = case ext
			when "pdf" then "application/pdf"
			when "xml" then "text/xml" # application/xml
			when "mxml" then "application/xv+xml"
			when "png" then "image/png"
			when "jpg" then "image/jpeg"
			when "jpeg" then "image/jpeg"
			when "jpgv" then "video/jpeg"
			when "html" then "text/html"
			when "css" then "text/css"
			when "js" then "application/javascript"
			when "json" then "application/json"
			when "fvt" then "video/vnd.fvt"
			when "f4v" then "video/x-f4v"
			when "flv" then "video/x-flv"
			when "gif" then "image/gif"
			when "h261" then "video/h261"
			when "h263" then "video/h263"
			when "h264" then "video/h264"
			when "ico" then "image/x-icon"
			when "m4v" then "video/x-m4v"
			when "mpga" then "audio/mpeg"
			when "mpeg" then "video/mpeg"
			when "mp4a" then "audio/mp4"
			when "mp4" then "video/mp4"
			when "psd" then "image/vnd.adobe.photoshop"
			when "svg" then "image/svg+xml"
			when "svgz" then "image/svg+xml"
			when "txt" then "text/plain"
			when "svg" then "image/svg+xml"
			when "ttf" then "application/x-font-ttf"
			when "otf" then "application/x-font-opentype"
			when "woff" then "application/font-woff"
			when "woff2" then "application/font-woff2"
			when "eot" then "application/vnd.ms-fontobject"
			when "sfnt" then "application/font-sfnt"

		end
	end

end

def help
  print " 
  You must pass the path to the Jupiter article folder to launch. 
  The path can be to the root of the Jupiter article folder or to the desired index file with in that folder. 

  Usage: #{__FILE__} article_directory - will use an article.xml or index.html file, which ever is found first as the articel index.
  The script will only look in the top level of the folder provided to the script. If the script cannot find an expected index file 
  it will display all html files found at the root of the folder and prompt you to select the html file to use as the index.
  
  You can also pass the path to the desired Jupiter article index file.
  Usage: #{__FILE__} path to article index in the root of the article folder - the index must be named article.xml or 
  be an html file.
  
" 
end

if ARGV.empty?
  # help
  exit
end

l = ManifestMaker.new
target = ARGV.join ' ' 
l.run target 
