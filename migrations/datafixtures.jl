module WIAGwebData

using MySQL
using Infiltrator
using DataFrames

dbwiag = nothing

function setDBWIAG(pwd = missing, host = "127.0.0.1", user = "wiag", db = "wiag") 
    global dbwiag
    if ismissing(pwd)
        println("Passwort für User ", user)
        pwd = readline()
    end    
    dbwiag = DBInterface.connect(MySQL.Connection, host, user, pwd, db = db)
end

"""
    updatenamevariant()

obsolete: see namelookup
"""
function updatenamevariant(fieldsrc::AbstractString, tablename::AbstractString)::Int
    dbwiag = DBInterface.connect(MySQL.Connection, "localhost", "wiag", "Wogen&Wellen", db="wiag");


    DBInterface.execute(dbwiag, "DELETE FROM " * tablename);

    # Do not use the same DB connection with an open cursor to insert data
    dfsrc = DBInterface.execute(dbwiag,
                                "SELECT wiagid, " * fieldsrc
                                * " FROM person") |> DataFrame;

    tblid = 1
    for row in eachrow(dfsrc)
        id, fns = row
        # println("id: ", id)
        # println(fns)

        if ismissing(fns) || fns == "" continue end
        for nv in split(fns, r",|;")
            insertstmt = ("INSERT INTO " * tablename * " VALUES ("
                          * string(tblid) * ","
                          * string(id) * ","
                          * "'" * strip(nv) * "')")
            # println(insertstmt)
            DBInterface.execute(dbwiag, insertstmt)
            tblid += 1;
        end
    end
    return tblid;
end

"""
    fillera(tblera::AbstractString, tblperson::AbstractString, tbloffice::AbstractString, colnameid::AbstractString, datereference=false)::Int

Compute earliest and latest date for each person. 

Take fields `date_hist_first` and `date_hist_last` into account if `datereference` is set to `true`.
"""
function fillera(tblera::AbstractString,
                 tblperson::AbstractString,
                 tbloffice::AbstractString,
                 colnameid = "id",
                 colnameidinoffice = "id_person";
                 datereference = false)::Int
    global dbwiag
    if isnothing(dbwiag)
        error("There is no valid database connection. Use `setDBWIAG'.")
    end
    
    DBInterface.execute(dbwiag, "DELETE FROM " * tblera);

    if datereference
        sqlselect = "SELECT " * colnameid * " as idperson, " *
            " date_birth, date_death, date_hist_first, date_hist_last " *
            " FROM " * tblperson;
    else
        sqlselect = "SELECT " * colnameid * " as idperson, date_birth, date_death " * " FROM " * tblperson;
    end
    
    dfperson = DBInterface.execute(dbwiag, sqlselect) |> DataFrame;

    rgxyear = r"[1-9][0-9][0-9]+";
    rgxcentury = r"([1-9][0-9])?\. [Jahrh|Jhd]"
    tblid = 0;

    # officestmt = DBInterface.prepare(dbwiag, "SELECT date_start, date_end FROM office"
    #                                  * " WHERE wiagid_person = ?")

    sqlselect = "SELECT " * colnameidinoffice * ", date_start, date_end " * " FROM " * tbloffice
    dfoffice = DBInterface.execute(dbwiag, sqlselect) |> DataFrame

    function parsemaybe(s)::Union{Missing, Int}
        r = missing
        if !ismissing(s)
            rgm = match(rgxyear, s)
            if !isnothing(rgm)
                r = parse(Int, rgm.match)
            else
                rgm = match(rgxcentury, s)
                if !isnothing(rgm) && !isnothing(rgm[1])
                    r = parse(Int, rgm[1]) * 100 - 50
                end                
            end            
        end
        return r
    end

    csqlvalues = String[]
    for row in eachrow(dfperson)
        erastart = Inf
        eraend = -Inf
        
        idperson, datebirth, datedeath = row[[:idperson, :date_birth, :date_death]]

        vcand = parsemaybe(datebirth)
        if !ismissing(vcand) erastart = vcand end

        vcand = parsemaybe(datedeath)
        if !ismissing(vcand) eraend = vcand end

        if datereference
            datehistfirst, datehistlast = row[[:date_hist_first, :date_hist_last]]
            vcand = parsemaybe(datehistfirst)
            if !ismissing(vcand) && vcand < erastart
                erastart = vcand
            end
            vcand = parsemaybe(datehistlast)
            if !ismissing(vcand) && vcand > eraend
                eraend = vcand
            end
        end        

        # println(wiagid, " ", typeof(dfoffice[:wiagid_person]))
        ixperson = dfoffice[:, colnameidinoffice] .== string(idperson)

        dfofficeperson = dfoffice[ixperson, :];
        for oc in eachrow(dfofficeperson)
            datestart = oc[:date_start]
            dateend = oc[:date_end]

            vcand = parsemaybe(datestart)
            if !ismissing(vcand) && vcand < erastart
                erastart = vcand
            end

            vcand = parsemaybe(dateend)
            if !ismissing(vcand) && vcand > eraend
                eraend = vcand
            end

        end

        if erastart == Inf && eraend != -Inf
            erastart = eraend
        elseif erastart != Inf && eraend == -Inf
            eraend = erastart
        end

        sqlerastart = erastart == Inf ? "NULL" : "'" * string(erastart) * "'"
        sqleraend = eraend == -Inf ? "NULL" : "'" * string(eraend) * "'"

        # if !ismissing(erastartdb) && !(typeof(erastartdb) == Int)
        #     println("start: ", erastartdb)
        # end
        # if !ismissing(eraenddb) && !(typeof(eraenddb) == Int)
        #     println("end: ", eraenddb)
        # end

        push!(csqlvalues, "('" * string(idperson) * "', " * sqlerastart * ", " * sqleraend * ")")
        tblid += 1
    end

    sqlvalues = join(csqlvalues, ", ")
    DBInterface.execute(dbwiag, "INSERT INTO " * tblera * " VALUES " * sqlvalues)    

    # println(sqlvalues);
    
    return tblid
end

function updateofficedate(tablename::AbstractString)::Int
    dbwiag = DBInterface.connect(MySQL.Connection, "localhost", "wiag", "Wogen&Wellen", db="wiag");

    DBInterface.execute(dbwiag, "DELETE FROM " * tablename);

    dfoffice = DBInterface.execute(dbwiag,
                                   "SELECT wiagid, date_start, date_end FROM office") |> DataFrame;

    rgx = r"[1-9][0-9]+";
    tblid = 0;

    # officestmt = DBInterface.prepare(dbwiag, "SELECT date_start, date_end FROM office"
    #                                  * " WHERE wiagid_person = ?")

    insertstmt = DBInterface.prepare(dbwiag, "INSERT INTO " * tablename * " VALUES (?, ?, ?)")

    function parsemaybe(rgx, s)::Union{Missing, Int}
        r = missing
        if !ismissing(s)
            rgm = match(rgx, s)
            if !isnothing(rgm)
                r = parse(Int, rgm.match)
            end
        end
        return r
    end

    for row in eachrow(dfoffice)
        wiagid, date_start, date_end = row

        dstdate_start = parsemaybe(rgx, date_start)
        dstdate_end = parsemaybe(rgx, date_end)

        DBInterface.execute(insertstmt, (wiagid, dstdate_start, dstdate_end));

        tblid += 1
        # if tblid > 25 break end
    end

    
    return tblid
end

"""
    fillnamelookup(tablename::AbstractString)::Int
    
Fill `tablename` with combinations of givenname and familyname and their variants.
""" 
function fillnamelookup(tbllookup::AbstractString,
                        tblperson::AbstractString,
                        colnameid::AbstractString = "id")::Int
    msg = 200
    if isnothing(dbwiag)
        error("There is no valid database connection. Use `setDBWIAG'.")
    end

    DBInterface.execute(dbwiag, "DELETE FROM " * tbllookup);

    dfperson = DBInterface.execute(dbwiag,
                                   "SELECT " * colnameid * " as id_person, " *
                                   "givenname, prefix_name, familyname, givenname_variant, familyname_variant " *
                                   "FROM " * tblperson * " person") |> DataFrame;

    # SQL
    # INSERT INTO dsttable VALUES (NULL, 'id_person1', 'givenname1', 'prefix_name1', 'familyname1'),
    # ('NULL', 'id_person2', 'givenname2', 'prefix_name2', 'familyname2');
    # 
    # structure
    # gn[:] prefix fn|fnv
    # gn[1] prefix fn|fnv
    # gnv[:] prefix fn|fnv
    # gnv[1] prefix fn|fnv

    # In the web application choose a version with or without prefix.

    imsg = 0
    csqlvalues = String[]
    appendtosqlrow(row) = append!(csqlvalues, row)
    for row in eachrow(dfperson)
        idperson = row[:id_person]
        gn = row[:givenname]
        prefix = row[:prefix_name]
        fn = row[:familyname]
        gnv = row[:givenname_variant]
        fnv = row[:familyname_variant]

        fillnamelookupgn(idperson, gn, prefix, fn, fnv) |> appendtosqlrow
        
        if !ismissing(gnv) && gnv != ""
            # sets of givennames
            cgnv = split(gnv, r", *")
            for gnve in cgnv
                fillnamelookupgn(idperson, gnve, prefix, fn, fnv) |> appendtosqlrow
            end
        end
        imsg += 1

        if imsg % msg == 0
            println("write row: ", imsg)
        end
    end
    sqlvalues = join(csqlvalues, ", ")
    
    irowout = length(csqlvalues)

    DBInterface.execute(dbwiag, "INSERT INTO " * tbllookup * " VALUES " * sqlvalues)
    
    return irowout

end


"""
    striplabel(s::AbstractString)::AbstractString

remove labels in data fields ("Taufname: Karl")
"""
function striplabel(s::AbstractString)::AbstractString
    poslabel = findfirst(':', s)
    if !isnothing(poslabel)
        s = strip(s[poslabel + 1:end])
    end
    return s
end


function fillnamelookupgn(id_person, gn, prefix, fn, fnv)
    csql = String[]

    function pushcsql(gni, fni)
        sgni = ismissing(gni) ? "NULL" : String(striplabel(gni))
        sfni = ismissing(fni) ? "NULL" : String(striplabel(fni))
        prefix = ismissing(prefix) ? "NULL" : prefix
        if !ismissing(id_person)
            push!(csql, "(" * "NULL, " * "'" * string(id_person)
                  * "', '" * sgni * "', '" * prefix * "', '" * sfni * "')")
        else
            @warn "Missing ID for ", gni
        end        
    end

    pushcsql(gn, fn)
    cgn = split(gn);
    # more than one givenname
    if length(cgn) > 1
        pushcsql(cgn[1], fn)
    end

    # familyname variants
    if !ismissing(fnv) && fnv != ""
        cfnv = split(fnv, r", *")
        for fnve in cfnv
            pushcsql(gn, fnve)
            # more than one givenname
            if length(cgn) > 1
                pushcsql(cgn[1], fnve)
            end
        end
    end

    return csql
end
    

end
