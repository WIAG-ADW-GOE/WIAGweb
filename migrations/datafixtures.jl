using MySQL
using Infiltrator

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
    updateera(tablename::AbstractSting)::Int

Compute earliest and latest date for each person.
"""
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


    for row in eachrow(dfperson)
        erastart = Inf
        eraend = -Inf
        wiagid, datebirth, datedeath = row

        vcand = parsemaybe(rgx, datebirth)
        if !ismissing(vcand) erastart = vcand end

        vcand = parsemaybe(rgx, datedeath)
        if !ismissing(vcand) eraend = vcand end

        # println(wiagid, " ", typeof(dfoffice[:wiagid_person]))
        ixperson = dfoffice[:wiagid_person] .== wiagid

        dfofficeperson = dfoffice[ixperson, :];
        for oc in eachrow(dfofficeperson)
            datestart = oc[:date_start]
            dateend = oc[:date_end]

            vcand = parsemaybe(rgx, datestart)
            if !ismissing(vcand) && vcand < erastart
                erastart = vcand
            end

            vcand = parsemaybe(rgx, dateend)
            if !ismissing(vcand) && vcand > eraend
                eraend = vcand
            end

        end

        if erastart == Inf && eraend != -Inf
            erastart = eraend
        elseif erastart != Inf && eraend == -Inf
            eraend = erastart
        end

        erastartdb = erastart == Inf ? missing : erastart
        eraenddb = eraend == -Inf ? missing : eraend

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

function striplabel(s::AbstractString)
    poslabel = findfirst(':', s)
    if !isnothing(poslabel)
        s = strip(s[poslabel + 1:end])
    end
    return s
end


let irowout = 1
    global fillnamelookupgn

    function fillnamelookupgn(insertstmt, wiagid, gn, prefix, fn, fnv)

        function dbinsert(gni, fni)
            sgni = ismissing(gni) ? missing : String(striplabel(gni))
            sfni = ismissing(fni) ? missing : String(striplabel(fni))
            DBInterface.execute(insertstmt, (irowout, wiagid, sgni, prefix, sfni))
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
