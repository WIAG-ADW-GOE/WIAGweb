using MySQL
using Infiltrator

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

function updateera(tablename::AbstractString)::Int
    dbwiag = DBInterface.connect(MySQL.Connection, "localhost", "wiag", "Wogen&Wellen", db="wiag");

    DBInterface.execute(dbwiag, "DELETE FROM " * tablename);

    dfperson = DBInterface.execute(dbwiag,
                                   "SELECT wiagid, date_birth, date_death FROM person") |> DataFrame;

    rgx = r"[1-9][0-9]+";
    tblid = 0;

    # officestmt = DBInterface.prepare(dbwiag, "SELECT date_start, date_end FROM office"
    #                                  * " WHERE wiagid_person = ?")

    dfoffice = DBInterface.execute(dbwiag, "SELECT wiagid_person, date_start, date_end FROM office") |> DataFrame;
    insertstmt = DBInterface.prepare(dbwiag, "INSERT INTO " * tablename * " VALUES (?, ?, ?)")

    for row in eachrow(dfperson)
        erastart = Inf
        eraend = -Inf
        wiagid, datebirth, datedeath = row
        rgm = match(rgx, datebirth)
        if rgm != nothing
            erastart = parse(Int, rgm.match)
        end

        rgm = match(rgx, datedeath)
        if rgm != nothing
            eraend = parse(Int, rgm.match)
        end
        ixperson = dfoffice[:wiagid_person] .== wiagid
        dfofficeperson = dfoffice[ixperson, :];
        for oc in eachrow(dfofficeperson)
            datestart = oc[:date_start]
            dateend = oc[:date_end]
            rgm = match(rgx, datestart)
            if rgm != nothing
                datestartint = parse(Int, rgm.match)
                if datestartint < erastart
                    erastart = datestartint
                end
            end
            rgm = match(rgx, dateend)
            if rgm != nothing
                dateendint = parse(Int, rgm.match)
                if dateendint > eraend
                    eraend = dateendint
                end
            end
            @infiltrate wiagid == "10028"
        end

        if erastart == Inf && eraend != -Inf
            erastart = eraend
        elseif erastart != Inf && eraend == -Inf
            eraend = erastart
        end

        erastartdb = erastart == Inf ? missing : erastart
        eraenddb = eraend == -Inf ? missing : eraend
        @infiltrate wiagid == "10028"

        if !ismissing(erastartdb) && !(typeof(erastartdb) == Int)
            println("start: ", erastartdb)
        end
        if !ismissing(eraenddb) && !(typeof(eraenddb) == Int)
            println("end: ", eraenddb)
        end

        DBInterface.execute(insertstmt, (wiagid, erastartdb, eraenddb));

        tblid += 1
        # if tblid > 25 break end
    end
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

    for row in eachrow(dfoffice)
        dstdate_start = missing
        dstdate_end = missing
        wiagid, date_start, date_end = row
        rgm = match(rgx, date_start)
        # println("Start: ", date_start)
        if rgm != nothing
            dstdate_start = parse(Int, rgm.match)
        end
        # println("Int: ", dstdate_start)

        rgm = match(rgx, date_end)
        if rgm != nothing
            dstdate_end = parse(Int, rgm.match)
        end

        DBInterface.execute(insertstmt, (wiagid, dstdate_start, dstdate_end));

        tblid += 1
        # if tblid > 25 break end
    end
    return tblid
end

function fillnamelookup(tablename::AbstractString)::Int
    msg = 200
    dbwiag = DBInterface.connect(MySQL.Connection, "localhost", "wiag", "Wogen&Wellen", db="wiag");

    DBInterface.execute(dbwiag, "DELETE FROM " * tablename);

    dfperson = DBInterface.execute(dbwiag,
                                   "SELECT wiagid, givenname, prefix_name, familyname, givenname_variant, familyname_variant FROM person") |> DataFrame;

    insertstmt = DBInterface.prepare(dbwiag, "INSERT INTO " * tablename * " VALUES (?, ?, ?, ?, ?)")

    # structure
    # gn[:] prefix fn|fnv
    # gn[1] prefix fn|fnv
    # gnv[:] prefix fn|fnv
    # gnv[1] prefix fn|fnv

    # app: test with or without prefix

    irowout = 0
    for row in eachrow(dfperson)
        wiagid = row[:wiagid]
        gn = row[:givenname]
        prefix = row[:prefix_name]
        fn = row[:familyname]
        gnv = row[:givenname_variant]
        fnv = row[:familyname_variant]



        irowout = fillnamelookupgn(insertstmt, wiagid, gn, prefix, fn, fnv)

        if !ismissing(gnv) && gnv != ""
            # sets of givennames
            cgnv = split(gnv, r", *")
            for gnve in cgnv
                irowout = fillnamelookupgn(insertstmt, wiagid, gnve, prefix, fn, fnv)
            end            
        end

        if irowout % msg == 0
            println("write row: ", irowout)
        end        

    end
    return irowout

end

let irowout = 1
    global fillnamelookupgn
    
    function fillnamelookupgn(insertstmt, wiagid, gn, prefix, fn, fnv)

        function dbinsert(gni, fni)
            DBInterface.execute(insertstmt, (irowout, wiagid, String(gni), prefix, String(fni)))
            irowout += 1
        end

        dbinsert(gn, fn)
        cgn = split(gn);
        # more than one givenname
        if length(cgn) > 1
            dbinsert(cgn[1], fn)
        end

        # familyname variants
        if !ismissing(fnv) && fnv != ""
            cfnv = split(fnv, r", *")
            for fnve in cfnv
                dbinsert(gn, fnve)
                # more than one givenname
                if length(cgn) > 1
                    dbinsert(cgn[1], fnve)
                end
            end
        end

        return(irowout)
    end

end
